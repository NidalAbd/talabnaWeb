#!/bin/bash
# Deploy built assets to server
# Usage: bash deploy-build.sh

echo "Building assets..."
npm run build

echo "Cleaning old admin assets on server..."
ssh -p 65002 u252000670@82.198.227.208 "rm -rf ~/domains/talbna.cloud/public/build/assets/admin-* ~/domains/talbna.cloud/public_html/build/assets/admin-*"

echo "Uploading build to server..."
scp -P 65002 -r public/build/* u252000670@82.198.227.208:~/domains/talbna.cloud/public/build/

echo "Syncing to public_html..."
ssh -p 65002 u252000670@82.198.227.208 "cp -rf ~/domains/talbna.cloud/public/build/* ~/domains/talbna.cloud/public_html/build/ && php ~/domains/talbna.cloud/artisan view:clear"

echo "Done! Hard refresh the admin panel (Ctrl+Shift+R)"
