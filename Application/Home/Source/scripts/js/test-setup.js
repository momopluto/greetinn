module ("Temperature conversion", {
	setup : function() {
		this.celsius1 = 20;
		this.celsius2 = 30;
		
		this.fahrenheit1 = 68;
		this.fahrenheit2 = 86;
	}
});
test("conversion to F", function(){
	var actual1 = convertFromCelsiusToFahrenheit(this.celsius1);
	equal(actual1, this.fahrenheit1);
	
	var actual2 = convertFromCelsiusToFahrenheit(this.celsius2);
	equal(actual2, this.fahrenheit2);
});
test("conversion to C", function(){
	var actual1 = convertFromFahrenheitToCelsius(this.fahrenheit1);
	equal(actual1, this.celsius1);
	
	var actual2 = convertFromFahrenheitToCelsius(this.fahrenheit2);
	equal(actual2, this.celsius2);

});

module ("Other assertion");
test("assertions", function(){
	ok(true);
	ok(3);
	strictEqual("3", "3");
	equal (3, "3");
});