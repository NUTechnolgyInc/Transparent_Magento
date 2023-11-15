# magento2-module
Magento plugin to invalidate TrasparentEdge CDN cache

## Installation and configuration

1. Add local composer package

```bash
composer require transparentcdn/transparentedge:@dev
```

2. Install Magento module

```bash
php bin/magento module:enable TransparentCDN_TransparentEdge
```

3. Run Magento setup upgrade

```bash
php bin/magento setup:upgrade
```

4. Flush Magento cache

```bash
php bin/magento cache:flush
```

5. Enable the Plugin from Back-end

Login To Magento Backend

Redirect to : 

STORES -> CONFIGURATIONS -> ADVANCED -> Transparent CDN
Enable Module -> YES
CompanyID (Store View) : < Your Company Id>
Client Key: < Your Client Key>
Secret Key: < Your Secret Key>

