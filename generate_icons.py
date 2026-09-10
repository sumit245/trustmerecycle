#!/usr/bin/env python3
"""
Generate all mobile app icon sizes from a source logo.
Usage: python3 generate_icons.py <path-to-logo.png>
"""
import sys, os
from PIL import Image, ImageDraw

if len(sys.argv) < 2:
    print("Usage: python3 generate_icons.py <logo.png>")
    sys.exit(1)

SRC = sys.argv[1]
BASE = os.path.dirname(os.path.abspath(__file__))
ANDROID_RES = os.path.join(BASE, "mobile/android/app/src/main/res")
IOS_ASSETS  = os.path.join(BASE, "mobile/ios/TrustMeRecycleMobile/Images.xcassets/AppIcon.appiconset")

logo = Image.open(SRC).convert("RGBA")

def square_crop(img):
    """Crop to square from center."""
    w, h = img.size
    s = min(w, h)
    left = (w - s) // 2
    top  = (h - s) // 2
    return img.crop((left, top, left + s, top + s))

def make_square(img, size, bg=(255, 255, 255, 255)):
    """Resize to exact square, white background."""
    src = square_crop(img).resize((size, size), Image.LANCZOS)
    canvas = Image.new("RGBA", (size, size), bg)
    canvas.paste(src, (0, 0), src)
    return canvas.convert("RGB")

def make_round(img, size):
    """Resize to square with circular mask."""
    src = square_crop(img).resize((size, size), Image.LANCZOS)
    mask = Image.new("L", (size, size), 0)
    ImageDraw.Draw(mask).ellipse((0, 0, size, size), fill=255)
    result = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    result.paste(src, (0, 0))
    result.putalpha(mask)
    canvas = Image.new("RGB", (size, size), (255, 255, 255))
    canvas.paste(result, mask=result.split()[3])
    return canvas

def make_adaptive_fg(img, size=432):
    """Adaptive icon foreground: logo centered in safe zone on transparent bg."""
    safe = int(size * 0.6667)   # 72/108 of canvas
    pad  = (size - safe) // 2
    src  = square_crop(img).resize((safe, safe), Image.LANCZOS)
    canvas = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    canvas.paste(src, (pad, pad), src)
    return canvas

def make_adaptive_bg(size=432, color=(44, 95, 45)):
    """Adaptive icon background: solid forest green."""
    return Image.new("RGB", (size, size), color)

def save(img, path):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    img.save(path, "PNG", optimize=True)
    print(f"  ✓ {os.path.relpath(path, BASE)}")

print("\n── Android launcher icons ──")
android_sizes = {
    "mipmap-mdpi":    48,
    "mipmap-hdpi":    72,
    "mipmap-xhdpi":   96,
    "mipmap-xxhdpi":  144,
    "mipmap-xxxhdpi": 192,
}
for folder, size in android_sizes.items():
    path = os.path.join(ANDROID_RES, folder)
    save(make_square(logo, size),   os.path.join(path, "ic_launcher.png"))
    save(make_round(logo, size),    os.path.join(path, "ic_launcher_round.png"))

print("\n── Android adaptive icon ──")
fg_dir = os.path.join(ANDROID_RES, "mipmap-anydpi-v26")
save(make_adaptive_fg(logo, 432),  os.path.join(ANDROID_RES, "mipmap-xxxhdpi/ic_launcher_foreground.png"))
save(make_adaptive_bg(432),        os.path.join(ANDROID_RES, "mipmap-xxxhdpi/ic_launcher_background.png"))

print("\n── Google Play Store icon (512×512) ──")
save(make_square(logo, 512), os.path.join(BASE, "icons_output/play_store_512.png"))

print("\n── iOS AppIcon sizes ──")
ios_sizes = [
    ("Icon-20@2x.png",   40),
    ("Icon-20@3x.png",   60),
    ("Icon-29@2x.png",   58),
    ("Icon-29@3x.png",   87),
    ("Icon-40@2x.png",   80),
    ("Icon-40@3x.png",  120),
    ("Icon-60@2x.png",  120),
    ("Icon-60@3x.png",  180),
    ("Icon-76.png",      76),
    ("Icon-76@2x.png",  152),
    ("Icon-83.5@2x.png",167),
    ("Icon-1024.png",  1024),
]
for name, size in ios_sizes:
    save(make_square(logo, size), os.path.join(IOS_ASSETS, name))

print("\n── All done! ──")
print(f"Play Store icon → icons_output/play_store_512.png")
