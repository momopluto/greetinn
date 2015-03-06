	// var price_url = "{:U('Home/Client/getPriceByStyle_Type')}";
	// var rooms_url = "{:U('Home/Client/getRoomsByDate_Type')}";

	/* 根据date和type，加载rooms */
	function loadRooms(){
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

			limit['type'] = $("#type").val();

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
	        			roomsHTML += subStr + '</select>';
	        			ROOM.html(roomsHTML);
	        		};
	        	}
	        })
		};
	}

	/* 根据style和type，加载price */
	function loadPrice(){
		var style = 0;
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
        		};
        	}
        })

        // loadRooms();// 加载空闲房间
	}

	/* 设置房型价格 */
	function setPrice(pricesJSON){
		var prices = eval(pricesJSON);

		var priceHTML = '<select id="price" name="price">';
		var PRICE = $("#price");

		var subStr = '<option value ="'+ prices['bid_price'] +'" selected="">标　价 | ￥'+ prices['bid_price'] +'</option>'+
					'<option value ="'+ prices['stu_price'] +'">学生价 | ￥'+ prices['stu_price'] +'</option>'+
					'<option value ="'+ prices['vip_price'] +'">会员价 | ￥'+ prices['vip_price'] +'</option>'+
					'<option value ="'+ prices['corp_price'] +'">协议价 | ￥'+ prices['corp_price'] +'</option>'+
					'<option value ="'+ prices['inner_price'] +'">内部价 | ￥'+ prices['inner_price'] +'</option>';
		/*switch(style) {
			case 0:
				subStr = '<option value ="'+ prices['bid_price'] +'" selected="">标　价 | ￥'+ prices['bid_price'] +'</option>'+
					'<option value ="'+ prices['stu_price'] +'">学生价 | ￥'+ prices['stu_price'] +'</option>'+
					'<option value ="'+ prices['vip_price'] +'">会员价 | ￥'+ prices['vip_price'] +'</option>'+
					'<option value ="'+ prices['corp_price'] +'">协议价 | ￥'+ prices['corp_price'] +'</option>'+
					'<option value ="'+ prices['inner_price'] +'">内部价 | ￥'+ prices['inner_price'] +'</option>';
				// alert(0);
				break;
			case 1:
				subStr = '<option value ="'+ prices['bid_price'] +'" selected="">标　价 | ￥'+ prices['bid_price'] +'</option>'+
					'<option value ="'+ prices['stu_price'] +'">学生价 | ￥'+ prices['stu_price'] +'</option>'+
					'<option value ="'+ prices['vip_price'] +'">会员价 | ￥'+ prices['vip_price'] +'</option>'+
					'<option value ="'+ prices['inner_price'] +'">特殊价 | ￥'+ prices['inner_price'] +'</option>';
				// alert(1);
				break;
			case 2:
				subStr = '<option value ="'+ prices['groupon_price'] +'" selected="">团购价 | ￥'+ prices['groupon_price'] +'</option>';
				break;
			default:
				alert('无此类型！');
				return;
				break;
		}*/

		priceHTML += subStr + '</select>';

		PRICE.html(priceHTML);
	}