

// var _type=0;/*默认房型为标单*/

/* 加载入住人信息 */
function loadInfo() {
	
	var ID = $("#ID");
	var ID_TIPS = $("#ID_tips");

	if (!isIDsLengthCorrect(ID.val())) {
		ID_TIPS.text("身份证长度不正确！应为18位！");
		$(".info").val('');/*将入住人信息置空*/
		ID.focus();
		return;
	};

	if (!isIDsLastCharCorrect(ID.val())) {
		ID_TIPS.text("身份证不合法！请检查是否输入正确！");
		$(".info").val('');/*将入住人信息置空*/
		ID.focus();
		return;
	};

	// ajax访问数据库查询顾客数据
	$.ajax({
    	url: getInfo_url,
    	type: 'post',
    	data: {ID: ID.val()},
    	dataType: 'json',
    	success: function(data) {

    		var info = data['info'];
			// console.log(info);

			if (info) {
				ID_TIPS.text("");

				$(":enabled#name_0").val(info['name']);/*姓名(一)*/
				$(":enabled#ID_0").val(info['ID_card']);/*身份证(一)*/
				$(":enabled#phone").val(info['phone']);/*手机*/
			}else {

				ID_TIPS.html("该身份证未注册！<a href='reg.html?id="+ID.val()+"'>注册</a>");
			}
    	}
    });
}

/* 加载价格&展示顾客信息输入表单 */
function loadPrice_N_showClientInfoInput(){
	// 将字符串转化为int
	style = parseInt($("#style").val());
	type = parseInt($("input[name=type]:checked").val());

	// _type = type;/*赋值给全局变量*/

	showClientInfoInput(type);
	loadPrice(style, type);
}

/* 展示顾客信息输入表单 */
function showClientInfoInput(type){

	var SINGLE = $("#single-info");
	var DOUBLE = $("#double-info");
	var MULTI = $("#multi-info");

	switch(type)
	{
	case 0:/*标单*/
	case 2:/*豪单*/
	case 6:/*青旅*/
	  // alert("单人");
	  if (typeof(DOUBLE.attr('disabled'))=="undefined") {
	  	DOUBLE.attr('disabled','');
	  	DOUBLE.attr('hidden','');
	  };
	  if (typeof(MULTI.attr('disabled'))=="undefined") {
	  	MULTI.attr('disabled','');
	  	MULTI.attr('hidden','');
	  };

	  if (typeof(SINGLE.attr('disabled'))!=="undefined") {
	  	SINGLE.removeAttr('disabled');
	  	SINGLE.removeAttr('hidden');
	  };
	  break;
	case 1:/*标双*/
	case 3:/*豪双*/
	  // alert("双人");
	  if (typeof(SINGLE.attr('disabled'))=="undefined") {
	  	SINGLE.attr('disabled','');
	  	SINGLE.attr('hidden','');
	  };
	  if (typeof(MULTI.attr('disabled'))=="undefined") {
	  	MULTI.attr('disabled','');
	  	MULTI.attr('hidden','');
	  };
	  
	  if (typeof(DOUBLE.attr('disabled'))!=="undefined") {
	  	DOUBLE.removeAttr('disabled');
	  	DOUBLE.removeAttr('hidden');
	  };
	  break;
	case 4:/*大复式*/
	case 5:/*小复式*/
	  // alert("复式");
	  if (typeof(SINGLE.attr('disabled'))=="undefined") {
	  	SINGLE.attr('disabled','');
	  	SINGLE.attr('hidden','');
	  };
	  if (typeof(DOUBLE.attr('disabled'))=="undefined") {
	  	DOUBLE.attr('disabled','');
	  	DOUBLE.attr('hidden','');
	  };

	  if (typeof(MULTI.attr('disabled'))!=="undefined") {
	  	MULTI.removeAttr('disabled');
	  	MULTI.removeAttr('hidden');
	  };
	  break;
	default:
	  alert("非法的房间类型！");
	}
}

/* 根据style和type，加载price */
function loadPrice(style, type){

	$.ajax({
    	url: getPrice_url,
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

	// 重置选房
    $("#room").html('<select id="room" name="room">'
        							+'<option value ="-1">预分配房间</option>'
        			+'</select>');
}

/* 设置房型价格 */
function setPrice(style, prices){

	// var PRICE = $("#price");
	var priceHTML = '';

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
			var BID = $(".price#bid")
			var STU = $(".price#stu")
			var VIP = $(".price#vip")

			BID.val(prices['bid_price']);
			BID.next("span").text(prices['bid_price']);

			STU.val(prices['stu_price']);
			STU.next("span").text(prices['stu_price']);

			VIP.val(prices['vip_price']);
			VIP.next("span").text(prices['vip_price']);

			showTotalPrice_N_leaveTime();
			
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

	/*默认加载房型为标单*/
	var type = $("input[name=type]:checked").val();
	var aDay = $("#aDay").val();
	var bDay = $("#bDay").val();

	if (aDay && bDay) {

		// var a = new Date(aDay.replace(/-/g, "/"));
		// var b = new Date(bDay.replace(/-/g, "/"));
		if (/*parseInt($("#style").val()) != 1 && */(new Date(aDay.replace(/-/g, "/")) >= new Date(bDay.replace(/-/g, "/")))) {
			// 非钟点房，且入住时间错误
			alert("入住/离店日期错误！");
			return;
		}

		if (typeof o_id == "undefined") {
			o_id = '';
		}
		// console.log(o_id);

		$.ajax({
        	url: getRoom_url,
        	type: 'post',
        	data: {type: type, aDay: aDay, bDay: bDay, o_id: o_id},
        	dataType: 'json',
        	success: function(data) {

        		if (data['rooms']) {
        		
        			var rooms = data['rooms'];
        			var ROOM = $("#room");
        			
        			// console.log(rooms);

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

/* 根据选中的价格类型，相应展示信息 */
function showInfo(){
	var idName = $("input[name=price]:checked").attr('id');
	// console.log(idName);
	switch(idName)
	{
	case "bid":/*标价*/
	case "stu":/*学生价*/
	  hideInfo();
	  break;
	case "vip":/*会员价*/
	  var AGENT_INFO = $("#AGENT_info");
	  if (typeof(AGENT_INFO.attr('disabled'))=="undefined") {/* 存在代理价 */
        AGENT_INFO.attr('disabled','');
        AGENT_INFO.attr('hidden','');
	  };
	  var SPEC_INPUT = $("#SPEC_input");
      if (typeof(SPEC_INPUT.attr('disabled'))=="undefined") {/* 存在高级 */
        	SPEC_INPUT.attr('disabled','');
        	SPEC_INPUT.attr('hidden','');
      };
	  showVip();
	  break;
	case "agent":/*代理价*/
	  if ($("span#VIP_tips").length != 0) {/* 存在vipinfo */
	  	$("span#VIP_tips").text('');

	  	setWhen_not_vipmode();
	  };
	  var SPEC_INPUT = $("#SPEC_input");
      if (typeof(SPEC_INPUT.attr('disabled'))=="undefined") {/* 存在高级 */
        	SPEC_INPUT.attr('disabled','');
        	SPEC_INPUT.attr('hidden','');
      };
	  showAgent();
	  break;
	case "spec":/*高级*/
	  var AGENT_INFO = $("#AGENT_info");
	  if (typeof(AGENT_INFO.attr('disabled'))=="undefined") {/* 存在代理价 */
        AGENT_INFO.attr('disabled','');
        AGENT_INFO.attr('hidden','');
	  };
	  if ($("span#VIP_tips").length != 0) {/* 存在vipinfo */
	  	$("span#VIP_tips").text('');
	  	
	  	setWhen_not_vipmode();
	  };
	  showSpec();
	  break;

	case "groupon":/*团购价*/
		break;
	default:
	  alert("非法的价格！");
	}

	if (parseInt($("#style").val()) == 1) {/*钟点房*/

		showTotalPrice_N_leaveTime();
	};
}

/* 针对标价和学生价 */
function hideInfo(){

	var AGENT_INFO = $("#AGENT_info");
	if (typeof(AGENT_INFO.attr('disabled'))=="undefined") {/* 存在代理价 */
	  	AGENT_INFO.attr('disabled','');
	  	AGENT_INFO.attr('hidden','');
	};

	var SPEC_INPUT = $("#SPEC_input");
    if (typeof(SPEC_INPUT.attr('disabled'))=="undefined") {/* 存在高级 */
      	SPEC_INPUT.attr('disabled','');
      	SPEC_INPUT.attr('hidden','');
    };

	if ($("span#VIP_tips").length != 0) {/* 存在vipinfo */
		$("span#VIP_tips").text('');
		
		setWhen_not_vipmode();
	};
}

/* 展示会员卡信息，卡号+余额 */
function showVip(){

	if ($("span#VIP_tips").length != 0) {/* 存在vipinfo */
		$("span#VIP_tips").text('');
	};

	$.ajax({
    	url: getVipByID_url,
    	type: 'post',
    	data: {id: $("#ID").val()},
    	dataType: 'json',
    	success: function(data) {

    		var VIP_TIPS = $("#VIP_tips");
    		if (data['info']) {
    			// console.log(data['vipInfo']);
    			var vipInfo = data['info'];

    			var subStr = ' 会员卡号：<span style="color:#5bc0de">'+ vipInfo['card_ID'] +'</span>';

    			// 计算入住总价
    			var total = calTotalPrice();
    			if (vipInfo['balance'] < total) {
    				subStr +=' 余额：<span style="color:#d9534f">'+ vipInfo['balance'] +'</span>';
    			}else{
    				subStr +=' 余额：<span style="color:#5cb85c">'+ vipInfo['balance'] +'</span>';
    			}

				VIP_TIPS.html(subStr);

				// 会员卡支付
				setWhen_vipmode();

    		}else {
    			VIP_TIPS.text('此身份证未注册会员！');

    			setWhen_not_vipmode();
    		}
    	}
    });
}

/* 会员卡支付，相关设置 */
function setWhen_vipmode(){
	$("#VIP_mode").removeAttr('disabled');/*会员卡支付 去掉disabled*/
	$("input[name=mode]").get(2).checked=true;/*选中 会员卡支付*/
	$("#mode").attr('hidden','');/*隐藏 支付方式*/
	$("#paid").attr('hidden','');/*隐藏 付款状态*/
}

/* 非会员卡支付，相关设置 */
function setWhen_not_vipmode(){
	$("#VIP_mode").attr('disabled','');/*会员卡支付 加上disabled*/
	$("input[name=mode]").get(2).checked=false;/*去除选中 会员卡支付*/
	$("#mode").removeAttr('hidden');/*显示 支付方式*/
	$("#paid").removeAttr('hidden');/*显示 付款状态*/
}

/* 展示代理人信息 */
function showAgent(){
	var AGENT_INFO = $("#AGENT_info");
	if (typeof(AGENT_INFO.attr('disabled'))!=="undefined") {/*AGENT_info存在disabled属性*/
	  	AGENT_INFO.removeAttr('disabled');
	  	AGENT_INFO.removeAttr('hidden');
	};
}

/* 展示高级:输入价钱+密码 */
function showSpec(){
	var SPEC_INPUT = $("#SPEC_input");
	if (typeof(SPEC_INPUT.attr('disabled'))!=="undefined") {/*SPEC_input存在disabled属性*/
	  	SPEC_INPUT.removeAttr('disabled');
	  	SPEC_INPUT.removeAttr('hidden');
	};
}

/* 针对会员价，计算入住总价钱 */
function calTotalPrice(){

	var total
	if ($("#aDay").val() && $("#bDay").val()) {
		var time1 = new Date($("#aDay").val());
		var time2 = new Date($("#bDay").val()); 

		/* 
		*如果求的时间差为天数则除以864000000，如果是小时数则在这个数字上 
		*除以24，分钟数则再除以60，依此类推 
		*/
		var time_diff = Math.abs(time2.getTime() - time1.getTime());
		var nums;
		if (time_diff >= 86400000) {/*非钟点房*/
			nums = time_diff / 86400000;
			total = parseInt($("#vip").val()) * nums;
		}else{/*钟点房*/
			nums = time_diff / (86400000 / 24);/*计算小时*/
			total = parseInt($("#vip").val()) * (nums / 3);/*每3小时为1份*/
		}
		// alert(nums);
	}else{
		total = 0;
	}

	// alert(total);
	return total;
}

/* 检验"高级"密码 */
function verifySpecPwd(){


	$.ajax({
    	url: getVerifyResult_url,
    	type: 'post',
    	data: {pwd: hex_md5($("#verifyPwd").val())},
    	dataType: 'json',
    	success: function(data) {

    		// console.log(data);
    		if (data) {
    			showSuccessStatus();
    		}else {
    			showErrorStatus();
    		}
    	}
    });
}

/* 验证"高级"密码成功后的提示 */
function showSuccessStatus(){

	var V_STATUS = $("#verifyPwd-status");
	V_STATUS.removeClass('glyphicon-remove');/*去掉 错误图标*/
	V_STATUS.parent().removeClass('has-error');/*去掉 错误红色*/

	V_STATUS.addClass('glyphicon-ok');/*加上 成功图标*/
	V_STATUS.parent().addClass('has-success');/*加上 成功绿色*/
}

/* 验证"高级"密码失败后的提示 */
function showErrorStatus(){

	var V_STATUS = $("#verifyPwd-status");
	V_STATUS.removeClass('glyphicon-ok');/*去掉 成功图标*/
	V_STATUS.parent().removeClass('has-success');/*去掉 成功绿色*/

	V_STATUS.addClass('glyphicon-remove');/*加上 错误图标*/
	V_STATUS.parent().addClass('has-error');/*加上 错误红色*/
}

/* 得到(未来/过去)num个小时的时间，字符串 */
function now_add_hours(num){
	var dt = new Date();
	dt.setHours(dt.getHours() + num);

	var year = dt.getFullYear();
	var month = dt.getMonth()+1;
	if (month < 9) {
		month = "0" + month;
	};
	var day = dt.getDate();
	if (day < 10) {
		day = "0" + day;
	};
	var hour = dt.getHours();
	if (hour < 10) {
		hour = "0" + hour;
	};
	var min = dt.getMinutes();
	if (min < 10) {
		min = "0" + min;
	};

	var leaveTime = year + '-' + month + '-' + day + ' ' + hour + ':' + min;
	return leaveTime;
}

/* 钟点房，展示总价+退房时间 */
function showTotalPrice_N_leaveTime(){
	var price = $("input[name=price]:checked").val();
	var quantity = $("#quantity").val();

	var totalPrice = price * quantity;
	$("#totalPrice").text(totalPrice);

	var num = 3 * quantity;

	var leave = now_add_hours(num);
	$("#leaveTime").text(leave);

	$("#aDay").val(now_add_hours(0));
	$("#bDay").val(leave);
}