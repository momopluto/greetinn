
/*测试 - 检验身份证最后一位校验码是否正确*/
// (function testIsIDsLastCharCorrect (){
//     // var a = "441423199305165018";
//     var a = "441423199305165017";
    
//     if (isIDsLastCharCorrect (a) === true) {
//         console.log("Passed!");
//     } else {
//         console.log("Failed!");
//     }
// }());


// module ("检验身份证最后一位校验码是否正确");
// test("conversion to F", function(){
// 	var actual1 = convertFromCelsiusToFahrenheit(20);
// 	equal(actual1, 68, "Value not correct");
	
// 	var actual2 = convertFromCelsiusToFahrenheit(30);
// 	equal(actual2, 86, "Value not correct");
// });
// test("conversion to C", function(){
// 	var actual1 = convertFromFahrenheitToCelsius(68);
// 	equal(actual1, 20, "Value not correct");
	
// 	var actual2 = convertFromFahrenheitToCelsius(86);
// 	equal(actual2, 30, "Value not correct");
// });

module ("检验身份证是否合法");
test("assertions", function(){

	ok(isIDsLengthCorrect ("441423199305165018"), "身份证长度为18位");
	ok(isIDsLengthCorrect ("441423199"), "身份证长度为18位");

	ok(isIDsLastCharCorrect ("441423199305"), "最后一位检验码正确");
	ok(isIDsLastCharCorrect ("441423199305165017"), "最后一位检验码正确");
	ok(isIDsLastCharCorrect ("441423199305165016"), "最后一位检验码正确");
	ok(isIDsLastCharCorrect ("111111111111111110"), "最后一位检验码正确");
	
	ok(isPhoneValid("18826481053"), "手机号正确");
	ok(isPhoneValid("1882648105"), "手机号正确");
	ok(isDigit("1882648105asd"), "数字字符串");

	// ok(true);
	// ok(3);
	// strictEqual("3", "3");
	// equal (3, "3");
})