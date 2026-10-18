#!/usr/bin/env python3
"""Build a reproducible, allowlisted Joomla installer. No vendor files are read."""
from pathlib import Path
import hashlib
import json
import sys
import zipfile
import xml.etree.ElementTree as ET

root = Path(__file__).resolve().parents[1]
source = root / 'bgm-plugin'
manifest_name = 'bgm-yootheme-colors-extended.xml'
manifest = ET.parse(source / manifest_name).getroot()
version = manifest.findtext('version')
assert manifest.attrib == {'type': 'plugin', 'group': 'system', 'method': 'upgrade'}
assert manifest.find('namespace').text == 'Bgm\\Plugin\\System\\BgmYoothemeColorsExtended'
assert manifest.find('files/folder').attrib['plugin'] == 'bgm-yootheme-colors-extended'
assert not manifest.findall('updateservers')
allowed_files = {
    manifest_name, 'bgm-bootstrap.php', 'bgm-styler.php', 'bgm-licence.txt',
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
files = sorted(path for path in source.rglob('*') if path.is_file() and path.name != '.DS_Store')
actual_files = {path.relative_to(source).as_posix() for path in files}
if actual_files != allowed_files:
    raise RuntimeError(f'Package inventory mismatch. Missing: {sorted(allowed_files-actual_files)}. Unexpected: {sorted(actual_files-allowed_files)}')
for path in files:
    relative = path.relative_to(source)
    assert not path.is_symlink(), f'Symlink refused: {relative}'
    assert str(relative) in allowed_files, relative
    assert path.suffix in {'.php', '.less', '.xml', '.ini', '.txt'}, relative
    assert path.name != '.DS_Store'
dist = root / 'dist'
dist.mkdir(exist_ok=True)
archive = dist / f'bgm-yootheme-colors-extended-{version}.zip'
with zipfile.ZipFile(archive, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as output:
    for path in files:
        info = zipfile.ZipInfo(path.relative_to(source).as_posix(), (2026, 10, 18, 0, 0, 0))
        info.compress_type = zipfile.ZIP_DEFLATED
        info.create_system = 3
        info.external_attr = 0o100644 << 16
        output.writestr(info, path.read_bytes(), compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)
checksum = hashlib.sha256(archive.read_bytes()).hexdigest()
archive.with_suffix('.zip.sha256').write_text(f'{checksum}  {archive.name}\n')
print(json.dumps({'archive': str(archive), 'version': version, 'files': len(files), 'bytes': archive.stat().st_size, 'sha256': checksum}, indent=2))
