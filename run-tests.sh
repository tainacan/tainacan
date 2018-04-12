#!/bin/bash

echo "Running unit tests in PHPUnit..."
phpunit

echo "Running integration tests headless in Crypess..."
# the value 0 leaves the cypress headless
./run-cypress.sh 2
