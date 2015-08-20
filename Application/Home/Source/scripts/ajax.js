var getInfo_url = "/greetinn/index.php/Home/Client/getPeopleInfo.html";
var getPrice_url = "/greetinn/index.php/Home/Client/getPriceByStyle_Type.html";
var getRoom_url = "/greetinn/index.php/Home/Client/getRoomsByDate_Type.html";
var getAgent_url = "/greetinn/index.php/Home/Client/getAgent.html";
var getVipByID_url = "/greetinn/index.php/Home/Vip/getVipInfoBy_IDCard.html";
var getVipByCard_url = "/greetinn/index.php/Home/Vip/getVipInfo.html";

function info_ajax(id_card){

	$.ajax({
    	url: getInfo_url,
    	type: 'post',
    	data: {ID: id_card},
    	dataType: 'json',
    	success: function(data) {

    		return data['info'];
    	}
    });
}

function price_ajax(orderStyle,roomType){

	$.ajax({
    	url: getPrice_url,
    	type: 'post',
    	data: {style: orderStyle, type: roomType},
    	dataType: 'json',
    	success: function(data) {

    		return data['prices'];
    	}
    });
}

function room_ajax(roomType,startDay,endDay){

	$.ajax({
    	url: getRoom_url,
    	type: 'post',
    	data: {type: roomType, aDay: startDay, bDay: endDay/*, id: o_id*/},
    	dataType: 'json',
    	success: function(data) {

    		// console.log(data);return;

    		return data['rooms'];
    	}
    })
}

function agent_ajax(){

	$.ajax({
    	url: getAgent_url,
    	type: 'post',
    	// data: {style: style, type: type},
    	dataType: 'json',
    	success: function(data) {

    		return data['agents'];
    	}
    });
}

function vip_ID_ajax(id_card){

	$.ajax({
    	url: getVipByID_url,
    	type: 'post',
    	data: {id: id_card},
    	dataType: 'json',
    	success: function(data) {

    		return data['info'];
    	}
    });
}

function vip_card_ajax(card){

	$.ajax({
    	url: getVipByCard_url,
    	type: 'post',
    	data: {card: card},
    	dataType: 'json',
    	success: function(data) {

    		return data['info'];
    	}
    });
}