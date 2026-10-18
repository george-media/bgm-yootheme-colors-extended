#!/usr/bin/env python3
"""Local release-boundary regressions. Mock downloads are not native updater proof."""
import copy
import hashlib
import importlib.util
import io
import json
from pathlib import Path
import shutil
import tempfile
import unittest
from unittest.mock import Mock, patch
import urllib.error
import xml.etree.ElementTree as ET
import zipfile

BGM_ROOT = Path(__file__).resolve().parents[1]
bgm_spec = importlib.util.spec_from_file_location('bgm_feed', BGM_ROOT / 'bgm-scripts/bgm-feed.py')
bgm_feed = importlib.util.module_from_spec(bgm_spec)
bgm_spec.loader.exec_module(bgm_feed)
bgm_build = bgm_feed.bgm_build


class BgmResponse(io.BytesIO):
    status = 200

    def geturl(self):
        return 'https://release-assets.githubusercontent.com/synthetic?signature=private-redirect-sentinel'


class BgmDistributionTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory(prefix='bgm-distribution-')
        self.addCleanup(self.temporary.cleanup)
        self.root = Path(self.temporary.name)
        shutil.copytree(BGM_ROOT / 'bgm-plugin', self.root / 'bgm-plugin')
        self.version = ET.parse(self.root / 'bgm-plugin' / bgm_build.BGM_MANIFEST).findtext('version')

    def build(self):
        result = bgm_build.bgm_build(self.root, self.version)
        self.receipt_path = Path(result['receipt'])
        self.receipt = json.loads(self.receipt_path.read_text())
        self.package = Path(result['archive']).read_bytes()
        self.asset_url = f"{bgm_build.BGM_REPOSITORY_URL}/releases/download/v{self.version}/{self.receipt['archive']}"
        return result

    def test_exact_members_hashes_and_repeated_build_are_reproducible(self):
        self.build()
        first_receipt = self.receipt_path.read_bytes()
        first_package = self.package
        self.build()
        self.assertEqual(first_receipt, self.receipt_path.read_bytes())
        self.assertEqual(first_package, self.package)
        self.assertEqual(self.receipt['sha256'], hashlib.sha256(self.package).hexdigest())
        with zipfile.ZipFile(io.BytesIO(self.package)) as archive:
            self.assertEqual(len(archive.namelist()), 14)
            for name in archive.namelist():
                self.assertEqual(archive.read(name), (self.root / 'bgm-plugin' / name).read_bytes())
                self.assertEqual(self.receipt['member_sha256'][name], hashlib.sha256(archive.read(name)).hexdigest())
        self.assertEqual(self.receipt_path.with_suffix('.sha256').read_text(), f"{self.receipt['sha256']}  {self.receipt['archive']}\n")

    def test_expected_version_mismatch_writes_nothing(self):
        with self.assertRaisesRegex(ValueError, 'expect-version'):
            bgm_build.bgm_build(self.root, '9.9.9')
        self.assertFalse((self.root / 'dist').exists())

    def test_extra_source_and_symlink_are_refused(self):
        extra = self.root / 'bgm-plugin' / 'unexpected.php'
        extra.write_text('<?php /* outside allowlist */')
        with self.assertRaisesRegex(ValueError, 'inventory'):
            bgm_build.bgm_build(self.root)
        extra.unlink()
        extra.symlink_to(self.root / 'bgm-plugin' / bgm_build.BGM_MANIFEST)
        with self.assertRaisesRegex(ValueError, 'symlink'):
            bgm_build.bgm_build(self.root)
        self.assertFalse((self.root / 'dist').exists())

    def test_versions_cannot_supply_paths_or_ambiguous_stability(self):
        for version in ['../x', '0.1.1-BETA1', '0.1.1-beta0', '0.1.1-test1', '01.1.1', '', None, True]:
            with self.subTest(version=version), self.assertRaises(ValueError):
                bgm_build.bgm_stability(version)
        self.assertEqual(bgm_build.bgm_stability('0.1.1-beta2'), 'beta')
        self.assertEqual(bgm_build.bgm_stability('0.1.1'), 'stable')

    def test_preserved_prototype_does_not_acquire_a_feed(self):
        manifest_path = self.root / 'bgm-plugin' / bgm_build.BGM_MANIFEST
        manifest = ET.parse(manifest_path)
        manifest.find('version').text = '0.1.0'
        with self.assertRaisesRegex(ValueError, '0.1.0'):
            bgm_build.bgm_manifest_details(ET.tostring(manifest.getroot()))
        manifest.getroot().remove(manifest.find('updateservers'))
        manifest.write(manifest_path, encoding='utf-8', xml_declaration=True)
        result = bgm_build.bgm_build(self.root, '0.1.0')
        with self.assertRaisesRegex(ValueError, 'live update feed'):
            bgm_feed.bgm_load_receipt(Path(result['receipt']))

    def test_receipt_tampering_is_refused_before_network_access(self):
        self.build()
        for key, value in [('sha256', '0' * 64), ('bytes', 1), ('archive', '../private.zip'), ('version', None), ('extra', 'unexpected')]:
            with self.subTest(key=key):
                altered = copy.deepcopy(self.receipt)
                altered[key] = value
                self.receipt_path.write_text(json.dumps(altered))
                with patch.object(bgm_feed, 'bgm_open_asset') as network:
                    with self.assertRaises(ValueError):
                        bgm_feed.bgm_generate(self.receipt_path, self.asset_url)
                    network.assert_not_called()

    def test_only_exact_public_versioned_release_asset_is_accepted(self):
        self.build()
        for url in [self.asset_url + '?token=private', self.asset_url.replace('github.com/', 'github.com@evil.invalid/'),
                    self.asset_url.replace('/george-media/', '/another-owner/'), self.asset_url.replace('https:', 'http:'),
                    self.asset_url.replace('/releases/download/', '/archive/refs/tags/')]:
            with self.subTest(url=url), patch.object(bgm_feed, 'bgm_open_asset') as network:
                with self.assertRaisesRegex(ValueError, 'exact public'):
                    bgm_feed.bgm_generate(self.receipt_path, url)
                network.assert_not_called()

    def test_verified_bytes_produce_correct_explicit_feed_constraints(self):
        self.build()
        with patch.object(bgm_feed, 'bgm_open_asset', return_value=BgmResponse(self.package)) as network:
            result = bgm_feed.bgm_generate(self.receipt_path, self.asset_url)
        request = network.call_args.args[0]
        self.assertNotIn('Authorization', request.headers)
        self.assertNotIn('Cookie', request.headers)
        data = Path(result['feed']).read_bytes()
        update = ET.fromstring(data).find('update')
        self.assertEqual(update.findtext('version'), self.version)
        self.assertEqual(update.findtext('tags/tag'), self.receipt['stability'])
        self.assertEqual(update.findtext('element'), 'bgm-yootheme-colors-extended')
        self.assertEqual(update.findtext('type'), 'plugin')
        self.assertEqual(update.findtext('folder'), 'system')
        self.assertEqual(update.findtext('client'), 'site')
        self.assertEqual(update.findtext('downloads/downloadurl'), self.asset_url)
        self.assertEqual(update.find('targetplatform').attrib, {'name': 'joomla', 'version': r'6\.2\.0$'})
        self.assertEqual(update.findtext('php_minimum'), '8.3.0')
        self.assertEqual(update.findtext('sha256'), hashlib.sha256(self.package).hexdigest())
        self.assertNotIn('private-redirect-sentinel', data.decode() + json.dumps(result))

    def test_corrupt_short_or_oversized_public_download_cannot_replace_feed(self):
        self.build()
        output = self.root / 'existing-feed.xml'
        output.write_bytes(b'<updates />\n')
        for body in [b'x' + self.package[1:], self.package[:-1], self.package + b'x']:
            with self.subTest(size=len(body)), patch.object(bgm_feed, 'bgm_open_asset', return_value=BgmResponse(body)):
                with self.assertRaises(ValueError):
                    bgm_feed.bgm_generate(self.receipt_path, self.asset_url, output)
            self.assertEqual(output.read_bytes(), b'<updates />\n')

    def test_unavailable_asset_does_not_echo_provider_details_or_write_feed(self):
        self.build()
        with patch.object(bgm_feed, 'bgm_open_asset', side_effect=urllib.error.URLError('private-provider-sentinel')):
            with self.assertRaisesRegex(ValueError, '^Anonymous published installer download failed.$'):
                bgm_feed.bgm_generate(self.receipt_path, self.asset_url)
        self.assertFalse((self.receipt_path.parent / f'bgm-update-{self.version}.xml').exists())

    def test_redirects_must_remain_https_on_github_asset_hosts(self):
        self.build()
        for destination in ['http://github.com/asset', 'https://evil.invalid/asset']:
            response = BgmResponse(self.package)
            response.geturl = lambda: destination
            with self.subTest(destination=destination), patch.object(bgm_feed, 'bgm_open_asset', return_value=response):
                with self.assertRaisesRegex(ValueError, 'redirect destination'):
                    bgm_feed.bgm_generate(self.receipt_path, self.asset_url)

    def test_unsafe_intermediate_redirect_is_refused_before_next_request(self):
        for destination in ['http://github.com/asset', 'https://evil.invalid/asset', 'https://github.com:8443/asset', 'https://user@github.com/asset']:
            request = bgm_feed.urllib.request.Request('https://github.com/george-media/example')
            request.timeout = 30
            handler = bgm_feed.BgmAssetRedirectHandler()
            opener = Mock()
            handler.add_parent(opener)
            with self.subTest(destination=destination), self.assertRaisesRegex(ValueError, '^Published asset returned an unsafe redirect destination.$'):
                handler.http_error_302(request, io.BytesIO(), 302, 'Found', {'location': destination})
            opener.open.assert_not_called()

    def test_signed_github_asset_redirect_is_followed_without_auth_headers(self):
        destination = 'https://release-assets.githubusercontent.com/synthetic?signature=private-redirect-sentinel'
        request = bgm_feed.urllib.request.Request('https://github.com/george-media/example', headers={'Accept': 'application/octet-stream'})
        request.timeout = 30
        handler = bgm_feed.BgmAssetRedirectHandler()
        opener = Mock()
        handler.add_parent(opener)
        handler.http_error_302(request, io.BytesIO(), 302, 'Found', {'location': destination})
        redirected = opener.open.call_args.args[0]
        self.assertEqual(redirected.full_url, destination)
        self.assertNotIn('Authorization', redirected.headers)
        self.assertNotIn('Cookie', redirected.headers)
        with patch.object(bgm_feed.urllib.request, 'build_opener') as factory:
            bgm_feed.bgm_open_asset(request)
        self.assertIsInstance(factory.call_args.args[0], bgm_feed.BgmAssetRedirectHandler)
        factory.return_value.open.assert_called_once_with(request, timeout=30)

    def test_duplicate_zip_member_is_rejected(self):
        self.build()
        data = io.BytesIO(self.package)
        with zipfile.ZipFile(data, 'a') as archive:
            with self.assertWarns(UserWarning):
                archive.writestr(bgm_build.BGM_MANIFEST, archive.read(bgm_build.BGM_MANIFEST))
        with self.assertRaisesRegex(ValueError, 'inventory'):
            bgm_build.bgm_package_receipt(data.getvalue(), self.receipt['archive'])


if __name__ == '__main__':
    unittest.main(verbosity=2)
