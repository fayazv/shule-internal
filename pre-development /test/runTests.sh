# intended to be a simple test system, but this does not work yet
# because there is no mechanism to ignore the randomly generated ids,
# which are guaranteed to diff
php ContentAdministrationApiTest.php > TEMPOUT
echo "ContentAdministrationApiTest Start"
diff output/ContentAdministrationApiTest.out TEMPOUT
echo "ContentAdministrationApiTest End"

rm TEMPOUT
