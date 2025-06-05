qz.security.setCertificatePromise(function(resolve, reject) {
    console.log('📜 Obteniendo certificado del tenant...');
    
    fetch('/api/certificates-qztray/digital')
        .then(response => response.text())
        .then(certificate => {
            if (certificate && certificate.trim() && !certificate.includes('error') && certificate.includes('BEGIN CERTIFICATE')) {
                console.log('📜 Usando certificado específico del tenant');
                resolve(certificate);
            } else {
                console.log('📜 No hay certificado del tenant, usando demo');
                // Certificado demo de QZ como fallback
                resolve("-----BEGIN CERTIFICATE-----\n" +
"MIIECzCCAq+gAwIBAgIJANWFuGx90071MA0GCSqGSIb3DQEBCwUAMIGaMQswCQYD\n" +
"VQQGEwJVUzELMAkGA1UECAwCTlkxEjAQBgNVBAcMCUNhbmFzdG90YTEbMBkGA1UE\n" +
"CgwSUVogSW5kdXN0cmllcywgTExDMRswGQYDVQQLDBJRWiBJbmR1c3RyaWVzLCBM\n" +
"TEMxHDAaBgkqhkiG9w0BCQEWDXN1cHBvcnRAcXouaW8xEjAQBgNVBAMMCWxvY2Fs\n" +
"aG9zdDAeFw0xNjAzMTQxNTI0NDVaFw0yNjAzMTIxNTI0NDVaMIGaMQswCQYDVQQG\n" +
"EwJVUzELMAkGA1UECAwCTlkxEjAQBgNVBAcMCUNhbmFzdG90YTEbMBkGA1UECgwS\n" +
"UVogSW5kdXN0cmllcywgTExDMRswGQYDVQQLDBJRWiBJbmR1c3RyaWVzLCBMTEMx\n" +
"HDAaBgkqhkiG9w0BCQEWDXN1cHBvcnRAcXouaW8xEjAQBgNVBAMMCWxvY2FsaG9z\n" +
"dDCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBANKTVjZs6rCEDKJ1NnFz\n" +
"P2GNaOp4j8DQXW5ZQWPDMIqKAjd6WLG9EJGGIJgmx1e+Hdi4pPVJFYu5+yRE3d6d\n" +
"V5Fb4LhAT8ZYb/2TdX+EJ6LhG5FzWX7pJ8sBLaH5Y6d0f0o7cjOzUf5F+Wz3qA3Y\n" +
"-----END CERTIFICATE-----\n");
            }
        })
        .catch(err => {
            console.log('📜 Error obteniendo certificado:', err);
            console.log('📜 Usando certificado demo de fallback');
            resolve("-----BEGIN CERTIFICATE-----\n" +
"MIIECzCCAq+gAwIBAgIJANWFuGx90071MA0GCSqGSIb3DQEBCwUAMIGaMQswCQYD\n" +
"VQQGEwJVUzELMAkGA1UECAwCTlkxEjAQBgNVBAcMCUNhbmFzdG90YTEbMBkGA1UE\n" +
"CgwSUVogSW5kdXN0cmllcywgTExDMRswGQYDVQQLDBJRWiBJbmR1c3RyaWVzLCBM\n" +
"TEMxHDAaBgkqhkiG9w0BCQEWDXN1cHBvcnRAcXouaW8xEjAQBgNVBAMMCWxvY2Fs\n" +
"aG9zdDAeFw0xNjAzMTQxNTI0NDVaFw0yNjAzMTIxNTI0NDVaMIGaMQswCQYDVQQG\n" +
"EwJVUzELMAkGA1UECAwCTlkxEjAQBgNVBAcMCUNhbmFzdG90YTEbMBkGA1UECgwS\n" +
"UVogSW5kdXN0cmllcywgTExDMRswGQYDVQQLDBJRWiBJbmR1c3RyaWVzLCBMTEMx\n" +
"HDAaBgkqhkiG9w0BCQEWDXN1cHBvcnRAcXouaW8xEjAQBgNVBAMMCWxvY2FsaG9z\n" +
"dDCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBANKTVjZs6rCEDKJ1NnFz\n" +
"P2GNaOp4j8DQXW5ZQWPDMIqKAjd6WLG9EJGGIJgmx1e+Hdi4pPVJFYu5+yRE3d6d\n" +
"V5Fb4LhAT8ZYb/2TdX+EJ6LhG5FzWX7pJ8sBLaH5Y6d0f0o7cjOzUf5F+Wz3qA3Y\n" +
"-----END CERTIFICATE-----\n");
        });
});

qz.security.setSignaturePromise(function(toSign) {
    return function(resolve, reject) {
        console.log('🔐 Obteniendo clave privada del tenant...');
        
        fetch('/api/certificates-qztray/private')
            .then(response => response.text())
            .then(privateKey => {
                if (privateKey && privateKey.trim() && !privateKey.includes('error') && privateKey.includes('BEGIN PRIVATE KEY')) {
                    console.log('🔐 Usando clave privada específica del tenant');
                    try {
                        var pk = KEYUTIL.getKey(privateKey);
                        var sig = new KJUR.crypto.Signature({"alg": "SHA1withRSA"});
                        sig.init(pk);
                        sig.updateString(toSign);
                        var hex = sig.sign();
                        resolve(stob64(hextorstr(hex)));
                    } catch (err) {
                        console.error('❌ Error firmando con clave del tenant:', err);
                        reject(err);
                    }
                } else {
                    console.log('🔐 No hay clave del tenant, usando demo');
                    // Clave demo como fallback
                    var demoKey = "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDSk1Y2bOqwhAyi\ndTZxcz9hjWjqeI/A0F1uWUFjwzCKigI3elixvRCRhiCYJsdXvh3YuKT1SRWLufsk\nRN3enVeRW+C4QE/GWG/9k3V/hCei4RuRc1l+6SfLAS2h+WOndH9KO3Izs1H+Rfls\n96gN2A==\n-----END PRIVATE KEY-----\n";
                    
                    try {
                        var pk = KEYUTIL.getKey(demoKey);
                        var sig = new KJUR.crypto.Signature({"alg": "SHA1withRSA"});
                        sig.init(pk);
                        sig.updateString(toSign);
                        var hex = sig.sign();
                        resolve(stob64(hextorstr(hex)));
                    } catch (err) {
                        reject(err);
                    }
                }
            })
            .catch(err => {
                console.log('🔐 Error obteniendo clave privada:', err);
                // Fallback a demo
                var demoKey = "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDSk1Y2bOqwhAyi\ndTZxcz9hjWjqeI/A0F1uWUFjwzCKigI3elixvRCRhiCYJsdXvh3YuKT1SRWLufsk\nRN3enVeRW+C4QE/GWG/9k3V/hCei4RuRc1l+6SfLAS2h+WOndH9KO3Izs1H+Rfls\n96gN2A==\n-----END PRIVATE KEY-----\n";
                
                try {
                    var pk = KEYUTIL.getKey(demoKey);
                    var sig = new KJUR.crypto.Signature({"alg": "SHA1withRSA"});
                    sig.init(pk);
                    sig.updateString(toSign);
                    var hex = sig.sign();
                    resolve(stob64(hextorstr(hex)));
                } catch (err) {
                    reject(err);
                }
            });
    };
});


/*
SI FUNCIONA
qz.security.setCertificatePromise(function(resolve, reject) {
    resolve("-----BEGIN CERTIFICATE-----\n" +
"MIIECzCCAvOgAwIBAgIGAZczKsIWMA0GCSqGSIb3DQEBCwUAMIGiMQswCQYDVQQG\n" +
"EwJVUzELMAkGA1UECAwCTlkxEjAQBgNVBAcMCUNhbmFzdG90YTEbMBkGA1UECgwS\n" +
"UVogSW5kdXN0cmllcywgTExDMRswGQYDVQQLDBJRWiBJbmR1c3RyaWVzLCBMTEMx\n" +
"HDAaBgkqhkiG9w0BCQEWDXN1cHBvcnRAcXouaW8xGjAYBgNVBAMMEVFaIFRyYXkg\n" +
"RGVtbyBDZXJ0MB4XDTI1MDYwMjAwMjIwOVoXDTQ1MDYwMjAwMjIwOVowgaIxCzAJ\n" +
"BgNVBAYTAlVTMQswCQYDVQQIDAJOWTESMBAGA1UEBwwJQ2FuYXN0b3RhMRswGQYD\n" +
"VQQKDBJRWiBJbmR1c3RyaWVzLCBMTEMxGzAZBgNVBAsMElFaIEluZHVzdHJpZXMs\n" +
"IExMQzEcMBoGCSqGSIb3DQEJARYNc3VwcG9ydEBxei5pbzEaMBgGA1UEAwwRUVog\n" +
"VHJheSBEZW1vIENlcnQwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC8\n" +
"QUWDozaltf5Dt71Xq6Klfnnn/rTtfB42InwXQ1JpZBrQ4pbO+cJBFrSGmR1BI1zp\n" +
"PhZHMieqvKRTp6r9CUkOkeDnEJn6kP1ejIh2KLGqTYLKCkdEOG/HSyx7STDM5oOq\n" +
"kKkCcYqQeISWWIedY5MSDKrffKidvQTU6OCYm9PS47nU4okDaYkMkgnj7SCsL9SK\n" +
"HBPK2Iutc9fP9gM4YziJeEUpOVL1gOZifTweNsY3BIxkX12vdARd5c2ATfgvwAUy\n" +
"En+KFtzpnbEvU2Vr1XX+UXOlpKp5b0venQHdHg5du+jIpSkIimGdS9DCZ5PCnFyj\n" +
"SG38Dr/SpJWYoHuqew+vAgMBAAGjRTBDMBIGA1UdEwEB/wQIMAYBAf8CAQEwDgYD\n" +
"VR0PAQH/BAQDAgEGMB0GA1UdDgQWBBSCLzmVlcvSek2rHejH7aKgb3O7pjANBgkq\n" +
"hkiG9w0BAQsFAAOCAQEAuQBFKRDIUW4+So3RMN3Yw2RTLs0aey6Ymg3QqMKlS5tk\n" +
"WHpuanMtbfqVQsP2a/BnEcEsaBVYmDt5LqynJ3bTB16QrOJ/Tq6I/KKT3KtB/bEy\n" +
"4Lru6IwI93RvMeoS1kpwLwl88JDL8JUvi71RifwDfy/+jjKPrZ7g+WN1xqz4+WYq\n" +
"hTmknNIApgVFv9dgs+cGSO5+ZbizyvAZYawe54XXCI53YMT30hnkbfe3grqyQUXF\n" +
"YBA5SfB1y1ndR/jh/Xx3DM34bt7tXzF9YXJo2o5aX3P5l96LZgYo+/T1WX+XNwK3\n" +
"su03w4k0abl2BPSowz1qu49ETHCTQcnsDBk5GaM5zQ==\n" +
"-----END CERTIFICATE-----\n");
});

privateKey = "-----BEGIN PRIVATE KEY-----\n" +
"MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQC8QUWDozaltf5D\n" +
"t71Xq6Klfnnn/rTtfB42InwXQ1JpZBrQ4pbO+cJBFrSGmR1BI1zpPhZHMieqvKRT\n" +
"p6r9CUkOkeDnEJn6kP1ejIh2KLGqTYLKCkdEOG/HSyx7STDM5oOqkKkCcYqQeISW\n" +
"WIedY5MSDKrffKidvQTU6OCYm9PS47nU4okDaYkMkgnj7SCsL9SKHBPK2Iutc9fP\n" +
"9gM4YziJeEUpOVL1gOZifTweNsY3BIxkX12vdARd5c2ATfgvwAUyEn+KFtzpnbEv\n" +
"U2Vr1XX+UXOlpKp5b0venQHdHg5du+jIpSkIimGdS9DCZ5PCnFyjSG38Dr/SpJWY\n" +
"oHuqew+vAgMBAAECggEAOAreXN/bxt01AofScCUCWG4ccHoc9o36mHcPpgU+pW1N\n" +
"pl2uM5OaxrGxsFgoo1mZsT3wd+VwdZ2O9fB2MLnw68t1vpPsovFC3EDN5w8aRO6q\n" +
"Pudsa9y5OgUhCtqxEm6VR9Ok3LtcWsHmBrP4O1yHTdpDjCCaOcspgxCIvCW3m5H4\n" +
"np3NLKixD8R5kR8xmv9DPwasxm2PMBzfDa9ervyC8VtLUnxkP46jwpSxO2EMPhHg\n" +
"FzrlEl7QpB8yZh7r0zpdgXIICxkbc0QLi5gHCGIOH+m5JrhgYBsZNghQd3M9G7wy\n" +
"URPSgm+dnT7cJJCgDUDeD+JrnFG7Ro0ZjVnZadZq8QKBgQDrHDqg06FJAnwQGGfm\n" +
"LJk5VKEdtQ9E7l51ggpQOow5NAJOI2Sn0gjnmarNxLP0tfJycgFE7JhObrv3+AEj\n" +
"Li+O6rasYDEP6F+LucRBxDLCAnurVeScKdsAyMyhPncum5B8WWMqLOLCjjob21f4\n" +
"QEwpRNy69Plcf2cKuyJcQx5RFQKBgQDM+0f94i7+tvRJ3hcn8S7CfaZDxqjK6QTc\n" +
"pEfcDp6g4x39WaeMGAPhD7l5+W6QVAlIF/X18kfe8I5xWIVIBupVoo8cPEdkIhRN\n" +
"7jrWtIGuyb1B8RkcL+J64pFV1kO4ienyXKZT6HWuKdtkSrIEWenPRLI/O+tTaxBn\n" +
"OKslQpJmswKBgQCsNFb06U1e7oT0PQwM2Wm5RjVkTvPKJ1Xkd8UaEmgWlfOCTAYz\n" +
"rXF8QV+Lq6GrgYD9NmebljfQaucervYWUIPhCCWYiDQnVKp26y/Gg/AxjiQK0LTL\n" +
"dRTFtE29ZMViy+q+SbKKd6n3mrkRIk2CtYWTTK7n+PqUN3S/tWVrcnXIKQKBgQCk\n" +
"a9eWfczGgki3y871OhAQ8Cri6MJSaNF+jsQZbxys3yEaLMUpqcXKzQsxHPQkD1SW\n" +
"oKmpy9r8qCcKIkBewzVK1adHtc5qMq/oxvQpbwcrBiWqdFN4+awIeB6uJL2TlAS1\n" +
"ZL4CRk/HEUorS4M53Emg+XClKlIcSqAQvDMEIz894wKBgQDI7blok8d7lejP2hjc\n" +
"j557/zfl8d6Ck06uv+UomAaPe0if4ONlmvSRuhjWtnoMAa/pLnhsMQGopnfcTUVz\n" +
"7fE1Fj0EDIXdpRRmcIPELriTfzjegBqod8VqKWwfMWJ/DlOerehE40QmHDfdV4e4\n" +
"K/jfQBmz6WEoW1Qdu3MtrHpNqA==\n" +
"-----END PRIVATE KEY-----\n";

qz.security.setSignaturePromise(function(toSign) {
    return function(resolve, reject) {
        try {
            var pk = KEYUTIL.getKey(privateKey);
            var sig = new KJUR.crypto.Signature({"alg": "SHA1withRSA"});
            sig.init(pk);
         sig.updateString(toSign);
            var hex = sig.sign();
            resolve(stob64(hextorstr(hex)));
        } catch (err) {
            reject(err);
        }
    };
});
*/
