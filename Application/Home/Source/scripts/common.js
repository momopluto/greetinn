/*检验身份证是否为18位*/
function isIDsLengthCorrect(IDStr){

	return IDStr.length == 18 ? true : false;
}

/*检验身份证最后一位校验码是否正确*/
function isIDsLastCharCorrect(IDStr){

	var map = Array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
	var sum = 0;
	for(var i = 17; i > 0; i--){
	    var s = Math.pow(2, i) % 11;
	    sum += s * IDStr[17-i];
	}
	
	return IDStr[IDStr.length - 1] == map[sum % 11] ? true : false;
}

/*判断字符串是否由数字组成*/
function isDigit(str){
	// var reg = /^d*$/; 
	var reg = new RegExp("^[0-9]+$");
	return reg.test(str); 
} 

/*检验手机号是否正确*/
function isPhoneValid(phone){

	return (isDigit(phone) && phone.length == 11) ? true : false;
}