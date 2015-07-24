

var _type;

/* 加载入住人信息 */
function loadInfo() {
	
	var ID = $("#ID");
	var ID_TIPS = $("#ID_tips");

	if (ID.val().length != 18) {
		ID_TIPS.text("身份证长度不正确！应为18位！");
		$(".info").val('');/*将入住人信息置空*/
		ID.focus();
		return;
	};


	$.ajax({
    	url: info_url,
    	type: 'post',
    	data: {ID: ID.val()},
    	dataType: 'json',
    	success: function(data) {

    		if (data['info'] === false) {
    			ID_TIPS.text("身份证不合法！请检查是否输入正确！");
    			$(".info").val('');/*将入住人信息置空*/
    			ID.focus();
    			return;
    		};

    		if (data['info']) {
    			ID_TIPS.text("");
    			// console.log(data['info']);
    			var info = eval(data['info']);

    			$("#name_0").val(info['name']);/*姓名(一)*/
    			$("#ID_0").val(info['ID_card']);/*身份证(一)*/
    			$("#phone").val(info['phone']);/*手机*/
    		}else{
    			ID_TIPS.text("");
    		}
    	}
    });
	
}

/* 根据style和type，加载price */
function loadPrice(style, type){
	_type = type;/*赋值给全局变量*/

	// alert("style=" + style + ", type=" + type);
	// return;

	if (type == 4 || type == 5) {/* 复式 */
		$("#number").removeAttr('hidden');
	}else{
		$("#number").attr('hidden', '');
	}

	$.ajax({
    	url: price_url,
    	type: 'post',
    	data: {style: style, type: type},
    	dataType: 'json',
    	success: function(data) {

    		if (data['prices']) {
    			// console.log(data['prices']);
    			setPrice(style, data['prices']);
    		};
    	}
    });

	// return;

    $("#room").html('<select id="room" name="room">'
        							+'<option value ="-1">预分配房间</option>'
        			+'</select>');

    if ($("#agent")) {/* 存在agent */
    	$("span#agent").remove();
    	$("select#agent").remove();
    };

    // loadRooms();// 加载空闲房间
}

/* 设置房型价格 */
function setPrice(style, priceJSON){
	var prices = eval(priceJSON);

	// alert(style);
	// return;

	// var PRICE = $("#price");
	var priceHTML = '';

	// 将字符串转化为int，使用switch
	style = parseInt(style);
	switch(style) {
		case 0:
		case 3:
		// 普通和(节假日)普通
			var BID = $(".price#bid")
			var STU = $(".price#stu")
			var AGENT = $(".price#agent")
			var VIP = $(".price#vip")

			BID.val(prices['bid_price']);
			BID.next("span").text(prices['bid_price']);

			STU.val(prices['stu_price']);
			STU.next("span").text(prices['stu_price']);

			AGENT.val(prices['agent_price']);
			AGENT.next("span").text(prices['agent_price']);

			VIP.val(prices['vip_price']);
			VIP.next("span").text(prices['vip_price']);

			break;
		case 1:
		// 钟点
			priceHTML = '<option value ="'+ prices['bid_price'] +'" selected="">标　价 | ￥'+ prices['bid_price'] +' /3小时</option>'+
				'<option value ="'+ prices['stu_price'] +'">学生价 | ￥'+ prices['stu_price'] +' /3小时</option>'+
				'<option value ="'+ prices['vip_price'] +'">会员价 | ￥'+ prices['vip_price'] +' /3小时</option>'+
				'<option value ="'+ prices['inner_price'] +'">特殊价 | ￥'+ prices['inner_price'] +' /1小时</option>';
			// alert(1);
			break;
		case 2:
		// 团购
			var GROUPON = $(".price#groupon")

			GROUPON.val(prices['groupon_price']);
			GROUPON.next("span").text(prices['groupon_price']);

			// priceHTML = '<option value ="'+ prices['groupon_price'] +'" selected="">团购价 | ￥'+ prices['groupon_price'] +'</option>';
			break;
		default:
			alert('无此类型！');
			return;
			break;
	}

	// PRICE.html(priceHTML);
}


/* 根据date和type，加载rooms */
function loadRooms(){

	if (!_type) {
		return;
	};

	var type = _type;

	var limit = Array();
	limit['aDay'] = $("#aDay").val();
	limit['bDay'] = $("#bDay").val();

	if (limit['aDay'] && limit['bDay']) {

		// var a = new Date(limit['aDay'].replace(/-/g, "/"));
		// var b = new Date(limit['bDay'].replace(/-/g, "/"));
		if (new Date(limit['aDay'].replace(/-/g, "/")) >= new Date(limit['bDay'].replace(/-/g, "/"))) {
			alert("入住/离店日期错误！");
			return;
		}

		limit['type'] = type;

		if (typeof o_id == "undefined") {
			o_id = '';
		}

		$.ajax({
        	url: rooms_url,
        	type: 'post',
        	data: {type: limit['type'], aDay: limit['aDay'], bDay: limit['bDay'], id: o_id},
        	dataType: 'json',
        	success: function(data) {

        		if (data['rooms']) {
        			
        			// alert("空闲房间加载完成！");
        			var rooms = eval(data['rooms']);
        			var ROOM = $("#room");
        			
        			console.log(rooms);

        			var roomsHTML = '<select id="room" name="room">'
        							+'<option value ="-1">预分配房间</option>';
					var subStr = '';
        			for (var i in rooms) {
        				subStr += '<option value ="'+ rooms[i] +'">'+ rooms[i] +'</option>';
        			}
        			if (rooms.length == 0) {
						subStr += '<option value ="-1">无空闲房间</option>';
					};

        			roomsHTML += subStr + '</select>';
        			ROOM.html(roomsHTML);
        		};
        	}
        })
	};
}

/* 展示代理人信息 */
function showAgent(){

	
	if ($("span#vipinfo").length != 0) {/* 存在vipinfo */
		$("span#vipinfo").remove();
		$("input[name=mode]").get(2).checked=false;
		$(".paid").removeAttr('hidden');
	};

	if ($("span#agentinfo").length != 0) {/* 存在agent */
		// 忽略，避免重复添加
		return;
	};

	var AGENT = $("#agent");
	$.ajax({
    	url: agent_url,
    	type: 'post',
    	// data: {style: style, type: type},
    	dataType: 'json',
    	success: function(data) {

    		if (data['agents']) {
    			// console.log(data['agents']);
    			var agents = data['agents'];
    			
    			var agentHTML = ' <span id="agentinfo">代理人：</span><select id="agentinfo" name="agent">';
    			var subStr = '';

    			for (var i in agents) {
    				// alert(agents[i]['name']);
    				subStr += '<option value ="'+ agents[i]['a_id'] +'">' + agents[i]['name'] +' | **'+ agents[i]['phone'].substr(7) + '</option>';
    			}

    			agentHTML += subStr + '</select>';

				AGENT.parent().after(agentHTML);
    		}else {

    			AGENT.parent().after(' <span id="agentinfo" style="color:red">无代理人数据！</span>');
    		}
    	}
    });
}

/* 展示会员卡信息，卡号+余额 */
function showVip(){

	if ($("span#agentinfo").length != 0) {/* 存在agent */
		$("span#agentinfo").remove();
		$("select#agentinfo").remove();
	};

	if ($("span#vipinfo").length != 0) {/* 存在vipinfo */
		$("span#vipinfo").remove();
	};

	$.ajax({
    	url: vipInfo_url,
    	type: 'post',
    	data: {id: $("#ID").val()},
    	dataType: 'json',
    	success: function(data) {

    		var VIP = $("#vip");
    		if (data['info']) {
    			// console.log(data['vipInfo']);
    			var vipInfo = data['info'];

    			var subStr = ' 会员卡号：<span style="color:green">'+ vipInfo['card_ID'] +'</span>';

    			// 计算入住总价
    			var total = calTotalPrice();
    			if (vipInfo['balance'] < total) {
    				subStr +=' 余额：<span style="color:red">'+ vipInfo['balance'] +'</span>';
    			}else{
    				subStr +=' 余额：<span style="color:green">'+ vipInfo['balance'] +'</span>';
    			}

    			var vipHTML = ' <span id="vipinfo" style="background:orange">' + subStr + '</span>';
    			
		        
				VIP.parent().after(vipHTML);

				// 会员卡支付
				$("input[name=mode]").get(2).checked=true; 
				$(".paid").attr('hidden','');
    		}else {

    			VIP.parent().after(' <span id="vipinfo" style="color:red">此身份证号未注册会员！</span>');
    			$("input[name=mode]").get(2).checked=false;
        		$(".paid").removeAttr('hidden');
    		}
    	}
    });
}

/* 针对会员价，计算入住总价钱 */
function calTotalPrice(){

	var total
	if ($("#aDay").val() && $("#bDay").val()) {
		var time1 = new Date($("#aDay").val());
		var time2 = new Date($("#bDay").val()); 

		/* 
		*如果求的时间差为天数则处以864000000，如果是小时数则在这个数字上 
		*除以24，分钟数则再除以60，依此类推 
		*/  
		var days = Math.abs(time2.getTime() - time1.getTime()) / 86400000;
		// alert(days);
		total = parseInt($("#vip").val()) * days;
	}else{
		total = 0;
	}

	// alert(total);
	return total;
}

/* 针对标价和学生价 */
function hideInfo(){

	if ($("span#agentinfo").length != 0) {/* 存在agent */
		$("span#agentinfo").remove();
		$("select#agentinfo").remove();
	};

	if ($("span#vipinfo").length != 0) {/* 存在vipinfo */
		$("span#vipinfo").remove();
		$("input[name=mode]").get(2).checked=false;
		$(".paid").removeAttr('hidden');
	};
}