#!/usr/bin/env python3
"""Build a reproducible, allowlisted Joomla installer. No vendor files are read."""
import argparse
import hashlib
import io
import json
from pathlib import Path
import re
import zipfile
import xml.etree.ElementTree as ET

BGM_MANIFEST = 'bgm-yootheme-colors-extended.xml'
BGM_FEED_URL = 'https://raw.githubusercontent.com/george-media/bgm-yootheme-colors-extended/main/bgm-updates/bgm-update.xml'
BGM_REPOSITORY_URL = 'https://github.com/george-media/bgm-yootheme-colors-extended'
BGM_MAX_PACKAGE_BYTES = 8 * 1024 * 1024
BGM_ALLOWED_FILES = {
    BGM_MANIFEST, 'bgm-bootstrap.php', 'bgm-styler.php', 'bgm-licence.txt',
    'services/provider.php',
    'src/Extension/BgmYoothemeColorsExtended.php',
    'src/Integration/BgmColourRegistry.php',
    'src/Integration/BgmModule.php',
    'src/Integration/BgmRenderAdapter.php',
    'src/Integration/BgmSchemaListener.php',
    'src/Integration/BgmView.php',
    'bgm-assets/bgm-less/bgm-colours.less',
    'language/en-GB/plg_system_bgm-yootheme-colors-extended.ini',
    'language/en-GB/plg_system_bgm-yootheme-colors-extended.sys.ini',
}


def bgm_stability(version):
    """Accept the version forms which PHP/Joomla compare in the intended order."""
    if not isinstance(version, str):
        raise ValueError('Unsupported package version.')
    match = re.fullmatch(r'(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-(alpha|beta|rc)[1-9]\d*)?', version or '')
    if not match:
        raise ValueError('Unsupported package version.')
    return match.group(1) or 'stable'


def bgm_manifest_details(data):
    manifest = ET.fromstring(data)
    version = manifest.findtext('version')
    stability = bgm_stability(version)
    if (manifest.tag != 'extension' or len(manifest.findall('version')) != 1
            or len(manifest.findall('namespace')) != 1
            or manifest.attrib != {'type': 'plugin', 'group': 'system', 'method': 'upgrade'}
            or manifest.findtext('namespace') != 'Bgm\\Plugin\\System\\BgmYoothemeColorsExtended'
            or manifest.find('namespace').attrib != {'path': 'src'}
            or manifest.find('files/folder') is None
            or manifest.find('files/folder').attrib != {'plugin': 'bgm-yootheme-colors-extended'}):
        raise ValueError('Native plugin manifest identity mismatch.')
    servers = manifest.findall('updateservers/server')
    if version == '0.1.0':
        if manifest.findall('updateservers'):
            raise ValueError('The preserved 0.1.0 prototype must not register an update server.')
        feed = None
    else:
        if (len(manifest.findall('updateservers')) != 1 or len(servers) != 1
                or servers[0].attrib != {'type': 'extension', 'priority': '1', 'name': 'BGM YOOTheme Colors Extended updates'}
                or servers[0].text != BGM_FEED_URL):
            raise ValueError('Release manifest must register the verified canonical update feed.')
        feed = BGM_FEED_URL
    return {'version': version, 'stability': stability, 'update_server': feed}


def bgm_archive_name(version):
    bgm_stability(version)
    return f'bgm-yootheme-colors-extended-{version}.zip'


def bgm_package_receipt(data, archive_name):
    """Derive release metadata from the exact ZIP, including every member's bytes."""
    if len(data) > BGM_MAX_PACKAGE_BYTES:
        raise ValueError('Package exceeds the release size limit.')
    with zipfile.ZipFile(io.BytesIO(data)) as archive:
        members = archive.infolist()
        names = [member.filename for member in members]
        if (len(names) != len(set(names)) or set(names) != BGM_ALLOWED_FILES
                or sum(member.file_size for member in members) > BGM_MAX_PACKAGE_BYTES
                or any(member.is_dir() or ((member.external_attr >> 16) & 0o170000) == 0o120000 for member in members)):
            raise ValueError('Native package member inventory mismatch.')
        details = bgm_manifest_details(archive.read(BGM_MANIFEST))
        if archive_name != bgm_archive_name(details['version']):
            raise ValueError('Archive filename and manifest version disagree.')
        hashes = {name: hashlib.sha256(archive.read(name)).hexdigest() for name in sorted(names)}
    return {
        'schema': 1,
        'archive': archive_name,
        **details,
        'bytes': len(data),
        'sha256': hashlib.sha256(data).hexdigest(),
        'member_sha256': hashes,
    }


def bgm_build(root, expected_version=None):
    source = root / 'bgm-plugin'
    details = bgm_manifest_details((source / BGM_MANIFEST).read_bytes())
    if expected_version is not None and details['version'] != expected_version:
        raise ValueError('Manifest version does not match --expect-version.')
    paths = sorted(source.rglob('*'))
    if any(path.is_symlink() for path in paths):
        raise ValueError('Source symlinks are not allowed in an installer.')
    files = [path for path in paths if path.is_file() and path.name != '.DS_Store']
    actual = {path.relative_to(source).as_posix() for path in files}
    if actual != BGM_ALLOWED_FILES:
        raise ValueError(f'Package inventory mismatch. Missing: {sorted(BGM_ALLOWED_FILES-actual)}. Unexpected: {sorted(actual-BGM_ALLOWED_FILES)}')
    data = io.BytesIO()
    with zipfile.ZipFile(data, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for path in files:
            info = zipfile.ZipInfo(path.relative_to(source).as_posix(), (2026, 10, 18, 0, 0, 0))
            info.compress_type = zipfile.ZIP_DEFLATED
            info.create_system = 3
            info.external_attr = 0o100644 << 16
            archive.writestr(info, path.read_bytes(), compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)
    package = data.getvalue()
    receipt = bgm_package_receipt(package, bgm_archive_name(details['version']))
    dist = root / 'dist'
    dist.mkdir(exist_ok=True)
    target = dist / receipt['archive']
    target.write_bytes(package)
    target.with_suffix('.zip.sha256').write_text(f"{receipt['sha256']}  {target.name}\n", encoding='utf-8')
    receipt_path = target.with_suffix('.zip.json')
    receipt_path.write_text(json.dumps(receipt, indent=2, sort_keys=True) + '\n', encoding='utf-8')
    return {'archive': str(target), 'receipt': str(receipt_path), 'version': receipt['version'],
            'files': len(receipt['member_sha256']), 'bytes': receipt['bytes'], 'sha256': receipt['sha256']}


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--expect-version', help='Refuse a build unless the source manifest has this exact version.')
    args = parser.parse_args()
    try:
        result = bgm_build(Path(__file__).resolve().parents[1], args.expect_version)
    except (ValueError, OSError, ET.ParseError, zipfile.BadZipFile) as error:
        parser.exit(1, f'Build refused: {error}\n')
    print(json.dumps(result, indent=2))


if __name__ == '__main__':
    main()
