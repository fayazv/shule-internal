# intended to be a simple test system, but this does not work yet
# because there is no mechanism to ignore the randomly generated ids,
# which are guaranteed to diff
php ContentAdministrationApiTest.php | sed 's/"id":[0-9]*/"id":/g' > TEMPOUT_GENERATED
cat output/ContentAdministrationApiTest.out | sed 's/"id":[0-9]*/"id":/g' > TEMPOUT_EXPECTED
echo "ContentAdministrationApiTest Start"
diff -s TEMPOUT_EXPECTED TEMPOUT_GENERATED
echo "ContentAdministrationApiTest End"

rm TEMPOUT_EXPECTED TEMPOUT_GENERATED
