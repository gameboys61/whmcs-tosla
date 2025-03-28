# WHMCS Tosla (AkÖde) Ödeme Modülü

Bu modül, WHMCS sisteminize **Tosla (eski adıyla AkÖde)** ödeme entegrasyonunu eklemenizi sağlar. Müşterileriniz, fatura ödeme ekranında bu ödeme yöntemini seçerek ödemelerini güvenli bir şekilde gerçekleştirebilirler.

## Kurulum

1. **Modül Dosyalarını Yükleyin:**
   - `modules/gateways/` içerisine modül dosyalarını kopyalayın.
   - Dosya yapısı aşağıdaki gibi olmalıdır:
     ```
     /modules/gateways/tosla.php
     /modules/gateways/callback/tosla.php
     ```

2. **WHMCS Yönetim Panelinde Modülü Etkinleştirin:**
   - **Ayarlar > Ödeme Yöntemleri** bölümüne gidin.
   - **Tosla (AkÖde) Ödeme Modülü**nü aktif hale getirin.
   - Tosla API bilgilerinizi girin ve kaydedin.

## Kullanım

- Müşteriniz fatura ödeme ekranına girdiğinde **Tosla ile Öde** seçeneğini görecektir.
- Butona tıkladığında bir **modal pencere** açılacaktır.
- Müşteri, açılan modal üzerinden kart bilgilerini girerek ödemesini tamamlayabilir.
