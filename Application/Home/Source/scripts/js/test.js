module ("Temperature conversion");
test("conversion to F", function(){
	var actual1 = convertFromCelsiusToFahrenheit(20);
	equal(actual1, 68, "Value not correct");
	
	var actual2 = convertFromCelsiusToFahrenheit(30);
	equal(actual2, 86, "Value not correct");
});
test("conversion to C", function(){
	var actual1 = convertFromFahrenheitToCelsius(68);
	equal(actual1, 20, "Value not correct");
	
	var actual2 = convertFromFahrenheitToCelsius(86);
	equal(actual2, 30, "Value not correct");
});

module ("Other assertion");
test("assertions", function(){
	ok(true);
	ok(3);
	strictEqual("3", "3");
	equal (3, "3");
})