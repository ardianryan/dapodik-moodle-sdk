# Panduan Kontribusi (Contributing Guidelines)

Terima kasih atas minat Anda berkontribusi pada pengembangan **`dapodik-moodle-sdk`**!

---

## 🛠️ Pengembangan Lokal

1. **Fork** repositori ini ke akun GitHub Anda.
2. **Clone** repositori fork:
   ```bash
   git clone https://github.com/ardianryan/dapodik-moodle-sdk.git
   cd dapodik-moodle-sdk
   ```
3. **Uji Coba Standalone Bridge**:
   ```bash
   cd standalone-bridge
   composer install
   ./vendor/bin/phpunit tests/BridgeTest.php
   ```

---

## 🌿 Standar Kode & Pull Request

1. Ikuti kaidah penulisan kode **PSR-12** untuk PHP dan kaidah **Moodle Coding Style** untuk plugin.
2. Pastikan tidak ada token rahasia atau data pribadi riil yang terunggah.
3. Gunakan konvensi commit *Conventional Commits* (`feat:`, `fix:`, `docs:`, dll.).
