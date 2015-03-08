	// var price_url = "{:U('Home/Client/getPriceByStyle_Type')}";
	// var rooms_url = "{:U('Home/Client/getRoomsByDate_Type')}";

	function loadRooms(){/* 加载房间 */
		var limit = Array();

		limit['type'] = $("#type").val();
		limit['aDay'] = $("#aDay").val();
		limit['bDay'] = $("#bDay").val();

		$.ajax({
        	url: rooms_url,
        	type: 'post',
        	data: {type: limit['type'], aDay: limit['aDay'], bDay: limit['bDay']},
        	dataType: 'json',
        	success: function(data) {

        		if (data['rooms']) {
        			
        			// alert("空间房间加载完成！");
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
	}

	function loadPrice(){/* 加载价格 */

		var style = 1;
		var type = $("#type").val();

		$.ajax({
        	url: price_url,
        	type: 'post',
        	data: {style: style, type: type},
        	dataType: 'json',
        	success: function(data) {

        		if (data['prices']) {
        			// console.log(data['prices']);
        			setPrice(data['prices']);

        			$("span#totalPrice").text("￥"+data['prices']['bid_price']);// 单独更新总价为标价
        		};
        	}
        });

        $("#room").html('<select id="room" name="room">'
	        							+'<option value ="-1">预分配房间</option>'
	        			+'</select>');
	}

	/* 设置房型价格 */
	function setPrice(pricesJSON){
		var prices = eval(pricesJSON);

		var priceHTML = '<select id="price" name="price">';
		var PRICE = $("#price");

		var subStr = '<option value ="'+ prices['bid_price'] +'" selected="">标　价 | ￥'+ prices['bid_price'] +'</option>'+
					'<option value ="'+ prices['stu_price'] +'">学生价 | ￥'+ prices['stu_price'] +'</option>'+
					'<option value ="'+ prices['vip_price'] +'">会员价 | ￥'+ prices['vip_price'] +'</option>'+
					'<option value ="'+ prices['inner_price'] +'">特殊价 | ￥'+ prices['inner_price'] +'</option>';

		priceHTML += subStr + '</select>';

		PRICE.html(priceHTML);
	}

	/* 设置总价和退房时间 */
	function setTotalPrice_leaveTime(){
		setTotalPrice();// 设置总价

		// 得到quantity.val, 当前时间
		// 计算得出退房时间，设置
		var leaveTime = now_add_hours(parseInt($("#quantity").val()));

		$("span#leaveTime").text(leaveTime);

		$("#aDay").val(now_add_hours(0));
		$("#bDay").val(now_add_hours(parseInt($("#quantity").val())));
	}

	/* 设置总价 */
	function setTotalPrice(){
		// 得到price.val, quantity.val
		// 计算得出总价，设置
		var totalPrice = $("#price").val() * $("#quantity").val();

		$("span#totalPrice").text("￥" + totalPrice);
		// $("input#totalPrice").val(totalPrice);
	}

	/* 得到未来addHours个小时的时间，字符串 */
	function now_add_hours(addHours){
		var dt = new Date();
		dt.setHours(dt.getHours() + addHours);

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