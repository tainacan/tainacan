touch report.txt
echo 'Verifing potencial incompatibilities with php 5.6 or greater'

echo 'W is Warning'
echo 'E is Error'

./src/vendor/bin/phpcs -p . --standard=PHPCompatibility --extensions=php --ignore=*/node_modules/*,*/src/vendor/* --report-file=./report.txt --runtime-set testVersion 5.6

