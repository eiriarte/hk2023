#!/bin/bash

./node_modules/hogan.js/bin/hulk --wraper amd templates/registration_price.es.mustache > public/scripts/registration_price.es.mustache.js
./node_modules/hogan.js/bin/hulk --wraper amd templates/registration_price.eo.mustache > public/scripts/registration_price.eo.mustache.js

echo 'OK!'