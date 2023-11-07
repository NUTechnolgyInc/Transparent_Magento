# magento2-module
Magento plugin to invalidate TrasparentEdge CDN cache

## Installation and configuration

1. Add the following code to composer.json from Magento root folder (to main level after "config"):

```json
"repositories": [
  {
    "type": "composer",
    "url": "https://composer.typo3.org/"
  },
  {
    "type": "path",
    "url": ".plugins/*"
  }
]
```

2. Create local packages folder (you need to be in Magento root folder):

```bash
mkdir -p .plugins/TransparentEdge
```

3. Clone repository to created folder

```bash
git clone git@github.com:NUTechnolgyInc/Transparent_Magento.git TransparentCDN/TransparentEdge
```

4. Add local composer package

```bash
composer require transparentcdn/transparentedge:@dev
```

5. Install Magento module

```bash
php bin/magento module:enable TransparentCDN_TransparentEdge
```

6. Run Magento setup upgrade

```bash
php bin/magento setup:upgrade
```

7. Flush Magento cache

```bash
php bin/magento cache:flush
```
