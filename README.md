<div align="center">
  <h1> 🐷 MobMorph 🪄 </h1>
  <p>Morph into a mob and use their abilities!</p>
</div>

## 📃 Description

Have you ever thought of surprising your friends by transforming into a mob, or would you like to be able to use a mob's abilities? Well now you can!

<div align="center">
  <img src="https://i.imgur.com/gnx6Y34.png" width="60%">
</div>

## 💡 Features
- Morph into a mob.
- Uses a mob's abilities.
- In-game `/mobmorph` command with `mobmorph.command` permission.
- A user-friendly UI Form.

# 🧩 Installation

1. Download the PHAR from [poggit](https://poggit.pmmp.io/ci/IvanCraft623/MobMorph/MobMorph) and paste it in the `plugins` directory of your server.
2. Download `Morph Addon` form [ModBay](https://modbay.org/mods/1757-morphing-bracelet.html#download-links-section) or [MCPEDL](https://mcpedl.com/morph-addon-alpha/#downloads).
3. Change addon extension from `.mcaddon` to `.zip`
4. Extract the addon ZIP file.
5. Only ZIP the `resource_pack` file, name the ZIP file whatever you want (eg. `MobMorphResourcePack.zip`). Your ZIP file stucture should look like this:
```
MobMorphResourcePack.zip
└── resource_pack
    ├── ...
    └── manifest.json
```
6. Paste the resource pack ZIP (eg. `MobMorphResourcePack.zip`) into the server's `resource_packs` folder.
7. In the `resource_packs/resource_packs.yml` file of your server add an entry to `resource_stack`. eg:
```yaml
resource_stack:
  - MobMorphResourcePack.zip
```
8. Start your server and enjoy!