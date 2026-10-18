#!/usr/bin/env python3
"""Generate a Joomla feed only after verifying its real, anonymous GitHub installer."""
import argparse
import hashlib
import http.client
import importlib.util
import json
from pathlib import Path
import tempfile
import urllib.error
import urllib.request
from urllib.parse import urlparse
import xml.etree.ElementTree as ET
import zipfile

bgm_spec = importlib.util.spec_from_file_location('bgm_build', Path(__file__).with_name('bgm-build.py'))
bgm_build = importlib.util.module_from_spec(bgm_spec)
bgm_spec.loader.exec_module(bgm_build)


def bgm_allowed_asset_destination(url):
    try:
        destination = urlparse(url)
        return (destination.scheme == 'https'
                and destination.hostname in {'github.com', 'release-assets.githubusercontent.com', 'objects.githubusercontent.com'}
                and destination.port in {None, 443}
                and destination.username is None and destination.password is None)
    except ValueError:
        return False


class BgmAssetRedirectHandler(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, request, response, code, message, headers, new_url):
        # Check every Location before urllib can send its next request. The final
        # response alone cannot prove that intermediate hops stayed on HTTPS.
        if not bgm_allowed_asset_destination(new_url):
            raise ValueError('Published asset returned an unsafe redirect destination.')
        return super().redirect_request(request, response, code, message, headers, new_url)


def bgm_open_asset(request):
    return urllib.request.build_opener(BgmAssetRedirectHandler()).open(request, timeout=30)


def bgm_load_receipt(path):
    if path.stat().st_size > 65536:
        raise ValueError('Release receipt exceeds its size limit.')
    receipt = json.loads(path.read_text(encoding='utf-8'))
    if not isinstance(receipt, dict):
        raise ValueError('Invalid release receipt.')
    version = receipt.get('version')
    name = bgm_build.bgm_archive_name(version)
    if receipt.get('archive') != name:
        raise ValueError('Release receipt has an invalid archive filename.')
    package_path = path.parent / name
    if package_path.stat().st_size > bgm_build.BGM_MAX_PACKAGE_BYTES:
        raise ValueError('Package exceeds the release size limit.')
    actual = bgm_build.bgm_package_receipt(package_path.read_bytes(), name)
    if receipt != actual:
        raise ValueError('Release receipt does not match the exact local installer.')
    if receipt['update_server'] != bgm_build.BGM_FEED_URL:
        raise ValueError('Package does not register the live update feed.')
    return receipt


def bgm_verify_asset(receipt, asset_url):
    expected = f"{bgm_build.BGM_REPOSITORY_URL}/releases/download/v{receipt['version']}/{receipt['archive']}"
    if asset_url != expected:
        raise ValueError('Asset URL must be the exact public versioned release installer URL.')
    request = urllib.request.Request(asset_url, headers={'User-Agent': 'BGM-release-verifier/1', 'Accept': 'application/octet-stream'})
    try:
        with bgm_open_asset(request) as response:
            if response.status != 200 or not bgm_allowed_asset_destination(response.geturl()):
                raise ValueError('Published asset returned an unexpected response or redirect destination.')
            checksum = hashlib.sha256()
            count = 0
            while chunk := response.read(min(65536, receipt['bytes'] - count + 1)):
                count += len(chunk)
                if count > receipt['bytes']:
                    raise ValueError('Published installer size does not match the exact local installer.')
                checksum.update(chunk)
    except (urllib.error.URLError, http.client.HTTPException, TimeoutError, OSError):
        raise ValueError('Anonymous published installer download failed.') from None
    if count != receipt['bytes'] or checksum.hexdigest() != receipt['sha256']:
        raise ValueError('Published installer checksum does not match the exact local installer.')


def bgm_feed_xml(receipt, asset_url):
    updates = ET.Element('updates')
    update = ET.SubElement(updates, 'update')
    for tag, text in [
        ('name', 'BGM YOOTheme Colors Extended'),
        ('element', 'bgm-yootheme-colors-extended'),
        ('type', 'plugin'), ('folder', 'system'), ('client', 'site'),
        ('version', receipt['version']),
    ]:
        ET.SubElement(update, tag).text = text
    downloads = ET.SubElement(update, 'downloads')
    ET.SubElement(downloads, 'downloadurl', {'type': 'full', 'format': 'zip'}).text = asset_url
    tags = ET.SubElement(update, 'tags')
    ET.SubElement(tags, 'tag').text = receipt['stability']
    # Both pinned Joomla 6.2.0 XML adapters prepend /^ to this native regex fragment.
    ET.SubElement(update, 'targetplatform', {'name': 'joomla', 'version': r'6\.2\.0$'})
    ET.SubElement(update, 'php_minimum').text = '8.3.0'
    ET.SubElement(update, 'sha256').text = receipt['sha256']
    ET.indent(updates, space='    ')
    return ET.tostring(updates, encoding='utf-8', xml_declaration=True) + b'\n'


def bgm_generate(receipt_path, asset_url, output_path=None):
    receipt = bgm_load_receipt(receipt_path)
    output_path = output_path or receipt_path.parent / f"bgm-update-{receipt['version']}.xml"
    if output_path.suffix != '.xml':
        raise ValueError('Feed output must be an XML file.')
    bgm_verify_asset(receipt, asset_url)
    data = bgm_feed_xml(receipt, asset_url)
    # Nothing is written before the public bytes have passed all identity/hash checks.
    temporary_path = None
    try:
        with tempfile.NamedTemporaryFile(dir=output_path.parent, prefix='bgm-feed-', delete=False) as temporary:
            temporary_path = Path(temporary.name)
            temporary.write(data)
        temporary_path.chmod(0o644)
        temporary_path.replace(output_path)
    finally:
        if temporary_path is not None:
            temporary_path.unlink(missing_ok=True)
    return {'status': 'verified', 'version': receipt['version'], 'stability': receipt['stability'],
            'asset_url': asset_url, 'bytes': receipt['bytes'], 'sha256': receipt['sha256'],
            'feed': str(output_path)}


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--receipt', required=True, type=Path, help='The deterministic builder .zip.json receipt, beside its ZIP.')
    parser.add_argument('--asset-url', required=True, help='The actual public GitHub release installer URL.')
    parser.add_argument('--output', type=Path, help='Local XML candidate output. Defaults to dist, without publishing it.')
    args = parser.parse_args()
    try:
        result = bgm_generate(args.receipt, args.asset_url, args.output)
    except (ValueError, OSError, ET.ParseError, zipfile.BadZipFile) as error:
        parser.exit(1, f'Feed refused: {error}\n')
    print(json.dumps(result, indent=2))


if __name__ == '__main__':
    main()
