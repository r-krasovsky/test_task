#!/bin/bash
cd public/js
ln -sf "../../vendor/components/bootstrap/js" "bootstrap"
ln -sf "../../vendor/components/jquery" "jquery"
ln -sf "../../vendor/datatables/datatables/media/js" "datatables"

cd ../css
ln -sf "../../vendor/components/bootstrap/css" "bootstrap"
ln -sf "../../vendor/datatables/datatables/media/css" "datatables"