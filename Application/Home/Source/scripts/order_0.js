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
		};
	}

	/* 根据style和type，加载price */
	function loadPrice(style){
		// var style = 0;
		var type = $("#type").val();

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

        $("#room").html('<select id="room" name="room">'
	        							+'<option value ="-1">预分配房间</option>'
	        			+'</select>');

        if ($("#agent")) {/* 存在agent */
        	$("span#agent").remove();
        	$("select#agent").remove();
        };

        // loadRooms();// 加载空闲房间
	}

	/* 展示代理人信息 */
	function showAgent(){


        var PRICE = $("#price");
		if (PRICE.after() != null) {
			// alert("已存在！");
			// return;
			PRICE.after('');
		};
        if (PRICE[0].selectedIndex == 3) {/* 协议价 */

			$.ajax({
	        	url: agent_url,
	        	type: 'post',
	        	// data: {style: style, type: type},
	        	dataType: 'json',
	        	success: function(data) {

	        		if (data['agents']) {
	        			// console.log(data['agents']);
	        			var agents = data['agents'];
	        			
	        			var agentHTML = ' <span id="agent">代理人：</span><select id="agent" name="agent">';
	        			var subStr = '';

	        			for (var i in agents) {
	        				// alert(agents[i]['name']);
	        				subStr += '<option value ="'+ agents[i]['a_id'] +'">' + agents[i]['name'] +' | **'+ agents[i]['phone'].substr(7) + '</option>';
	        			}

	        			agentHTML += subStr + '</select>';

    			        var SOURCE = $("#source");
    					SOURCE.after(agentHTML);
	        		};
	        	}
	        });
        }else{
        	if ($("#agent")) {/* 存在agent */
        		$("span#agent").remove();
        		$("select#agent").remove();
        	};

        	if (PRICE[0].selectedIndex == 2) {/* 会员价 */
        		$("#mode")[0].selectedIndex = 2;// 会员卡支付
        		$(".paid").attr('hidden','');
        	}else{
        		$("#mode")[0].selectedIndex = 0;
        		$(".paid").removeAttr('hidden');
        	}
        }
	}

	/* 设置房型价格 */
	function setPrice(style, pricesJSON){
		var prices = eval(pricesJSON);

		var priceHTML = '<select id="price" name="price">';
		var PRICE = $("#price");

		var subStr = '';

		switch(style) {
			case 0:
			case 3:
				subStr = '<option value ="'+ prices['bid_price'] +'" selected="">标　价 | ￥'+ prices['bid_price'] +'</option>'+
					'<option value ="'+ prices['stu_price'] +'">学生价 | ￥'+ prices['stu_price'] +'</option>'+
					'<option value ="'+ prices['vip_price'] +'">会员价 | ￥'+ prices['vip_price'] +'</option>'+
					'<option value ="'+ prices['agent_price'] +'">代理价 | ￥'+ prices['agent_price'] +'</option>'+
					// '<option value ="'+ prices['corp_price'] +'">协议价 | ￥'+ prices['corp_price'] +'</option>'+
					'<option value ="'+ prices['inner_price'] +'">内部价 | ￥'+ prices['inner_price'] +'</option>';
				// alert(0);
				break;
			/*case 1:
				subStr = '<option value ="'+ prices['bid_price'] +'" selected="">标　价 | ￥'+ prices['bid_price'] +'</option>'+
					'<option value ="'+ prices['stu_price'] +'">学生价 | ￥'+ prices['stu_price'] +'</option>'+
					'<option value ="'+ prices['vip_price'] +'">会员价 | ￥'+ prices['vip_price'] +'</option>'+
					'<option value ="'+ prices['inner_price'] +'">特殊价 | ￥'+ prices['inner_price'] +'</option>';
				// alert(1);
				break;*/
			case 2:
				subStr = '<option value ="'+ prices['groupon_price'] +'" selected="">团购价 | ￥'+ prices['groupon_price'] +'</option>';
				break;
			default:
				alert('无此类型！');
				return;
				break;
		}

		priceHTML += subStr + '</select>';

		PRICE.html(priceHTML);
	}

	/* 加载入住人信息 */
	function loadInfo() {
		
		var ID = $("#ID");
		var ID_TIPS = $("#ID_tips");

		if (ID.val().length != 18) {
			ID_TIPS.text("身份证长度不正确！应为18位！");
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