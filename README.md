# wili
Wili package collection

## How to compile a Wili Package
1. tar cfvz {PACKAGE-NAME}.tar.gz {PACKAGE-NAME}
2. openssl smime -encrypt -binary -aes256 -in {PACKAGE-NAME}.tar.gz -out {PACKAGE-NAME}.wili -outform DER wili.public.pem
