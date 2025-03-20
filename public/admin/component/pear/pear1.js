window.rootPath = (function (src) {
	src = document.currentScript
		? document.currentScript.src
		: document.scripts[document.scripts.length - 1].src;
	return src.substring(0, src.lastIndexOf("/") + 1);
})();
layui.config({
	base: rootPath + "module/",
	version: "3.9.9"
}).extend({
	admin: "admin", 	// 框架布局组件
	menu: "menu",		// 数据菜单组件
	frame: "frame", 	// 内容页面组件
	tab: "tab",			// 多选项卡组件
	echarts: "echarts", // 数据图表组件
	echartsTheme: "echartsTheme", // 数据图表主题
	encrypt: "encrypt",		// 数据加密组件
	select: "select",	// 下拉多选组件
	drawer: "drawer",	// 抽屉弹层组件
	notice: "notice",	// 消息提示组件
	step: "step",		// 分布表单组件
	tag: "tag",			// 多标签页组件
	popup: "popup",      // 弹层封装
	treetable: "treetable",   // 树状表格
	dtree: "dtree",			// 树结构
	tinymce: "tinymce/tinymce", // 编辑器
	area: "area",			// 省市级联  
	count: "count",			// 数字滚动
	topBar: "topBar",		// 置顶组件
	button: "button",		// 加载按钮
	design: "design",		// 表单设计
	card: "card",			// 数据卡片组件
	loading: "loading",		// 加载组件
	cropper: "cropper",		// 裁剪组件
	convert: "convert",		// 数据转换
	yaml: "yaml",			// yaml 解析组件
	context: "context",		// 上下文组件
	http: "http",			// ajax请求组件
	theme: "theme",			// 主题转换
	message: "message",     // 通知组件
	toast: "toast",         // 消息通知
	iconPicker: "iconPicker"// 图标选择
}).use(['layer', 'theme'], function () {
	layui.theme.changeTheme(window, false);
});

var ServerURL = '' //请求API域名
var LoginToken = '' //用户本地token
var LoginTokenName = '' //用户本地token



layui.use(['form', 'element', 'upload'], function () {
	var $ = layui.jquery;
	var upload = layui.upload;
	var element = layui.element;
	//获取本地配置文件
	$.ajax({
		type: "get",
		url: "/admin/config/pear.config.json",
		data: {},
		dataType: "json",
		async: false,
		success: function (response) {
			ServerURL = response.serverURL
			LoginToken = localStorage.getItem(response.loginTokenName) //用户本地token
			LoginTokenName = response.loginTokenName //用户本地token名称
		}
	});


	$.ajaxSetup({
		headers: {
			"login-token": LoginToken,
		}, success: function (result) {
			//console.log(result);
		},
		error: function (jqXHR) {
			switch (jqXHR.status) {
				case (500):
					layer.msg('请求服务错误', {
						icon: 2,
						time: 1000
					}, function () {
					});
					break;
				case (401):
					layer.msg(jqXHR.responseJSON.msg, {
						icon: 2,
						time: 1000
					}, function () {
						window.location = 'admin_basic_login.html';
					});
					break;
				case (403):
					layer.msg(jqXHR.responseJSON.msg, {
						icon: 2,
						time: 1000
					}, function () {

					});
					break;
				case (404):
					layer.msg('请求地址不存在', {
						icon: 2,
						time: 1000
					}, function () {
					});
					break;
				case (408):
					layer.msg('请求超时', {
						icon: 2,
						time: 1000
					}, function () {
					});
					break;
				default:
					layer.msg('未知错误', {
						icon: 2,
						time: 1000
					}, function () {
					});
			}
		},
	});

	window.uploadImage = function (obj, _elem, file_category = "all") {
		console.log(file_category);
		//常规使用 - 普通图片上传
		var uploadInst = upload.render({
			elem: "#" + _elem
			, url: "../Upload/upload" //此处用的是第三方的 http 请求演示，实际使用时改成您自己的上传接口即可。
			, data: { file_category: file_category }
			, before: function (obj) {
				//预读本地文件示例，不支持ie8
				obj.preview(function (index, file, result) {
					//$('#' + _elem + "_show").attr('src', result); //图片链接（base64）
				});

				element.progress(_elem + '_percent', '0%'); //进度条复位
				layer.msg('上传中', { icon: 16, time: 0 });
				$('#' + _elem + '_percent').show();
			}
			, done: function (res) {
			// console.log(res);

				//如果上传失败
				if (res.code != 200) {
					return layer.msg('上传失败');
				}
				//上传成功的一些操作
				obj.val(res.data.file_name)
				$('#' + _elem + "_show").attr('src', res.data.file_url); //图片链接（base64）
				$("#" + _elem + '_text').html(''); //置空上传失败的状态

			}
			, error: function () {
				//演示失败状态，并实现重传
				var demoText = $("#" + _elem + '_text');
				demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
				demoText.find('.demo-reload').on('click', function () {
					uploadInst.upload();
				});
			}
			//进度条
			, progress: function (n, elem, e) {
				element.progress(_elem + '_percent', n + '%'); //可配合 layui 进度条元素使用
				if (n == 100) {
					layer.msg('上传完毕', { icon: 1 });
					$('#' + _elem + '_percent').hide();
				}
			}
		});
	}

	//查看图片详情
	window.viewImage = function (obj) {
		var img = new Image()
		img.src = obj.src
		var height = img.height + 50; // 原图片大小
		var width = img.width; //原图片大小
		if (height > 800) {
			width = width * (800 / height);
			height = 800;
		}
		console.log(width, height)
		var imgHtml =
			"<img src='" +
			obj.src +
			"'  style='width:100%;height:100%'  />"
		//弹出层
		layer.open({
			type: 1,
			shade: 0.8,
			offset: 'auto',
			area: [500 + 'px', 550 + 'px'],
			area: [width + 'px', height + 'px'], //原图显示
			shadeClose: true,
			scrollbar: false,
			title: '查看图片', //不显示标题
			content: imgHtml, //捕获的元素，注意：最好该指定的元素要存放在body最外层，否则可能被其它的相对元素所影响
			cancel: function () {
				//layer.msg('捕获就是从页面已经存在的元素上，包裹layer的结构', { time: 5000, icon: 6 });
			},
		})
	}


	window.uploadFile = function (obj, _elem, file_category = "all") {
		//常规使用 - 普通图片上传
		var uploadInst = upload.render({
			elem: "#" + _elem
			, accept: 'file' //视频
			, url: ServerURL + "/Upload/upload" //此处用的是第三方的 http 请求演示，实际使用时改成您自己的上传接口即可。
			, data: { file_category: file_category }
			, before: function (obj) {
				//预读本地文件示例，不支持ie8
				obj.preview(function (index, file, result) {
					//$('#' + _elem + "_show").attr('src', result); //图片链接（base64）
				});
				$('#' + _elem + "_percent_div").css('display', 'block');
				element.progress(_elem + '_percent', '0%'); //进度条复位
				layer.msg('上传中', { icon: 16, time: 0 });
			}
			, done: function (res) {
				//如果上传失败
				if (res.code != 200) {
					return layer.msg(res.msg);
				}
				//上传成功的一些操作
				obj.val(res.data.file_url)
				$("#" + _elem + '_text').html(''); //置空上传失败的状态
				return layer.msg('上传成功');
			}
			, error: function () {
				//演示失败状态，并实现重传
				var demoText = $("#" + _elem + '_text');
				demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
				demoText.find('.demo-reload').on('click', function () {
					uploadInst.upload();
				});
			}
			//进度条
			, progress: function (n, elem, e) {
				element.progress(_elem + '_percent', n + '%'); //可配合 layui 进度条元素使用
				if (n == 100) {
					layer.msg('上传中', { icon: 1 });
				}
			}
		});
	}

	window.getQueryVariable = function (variable) {
		var query = window.location.search.substring(1);
		var vars = query.split("&");
		for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split("=");
			if (pair[0] == variable) { return pair[1]; }
		}
		return (false);
	}

	window.initializeSelect = function (url, node, data) {
		$.ajax({
			type: "POST",
			url: url,
			data: data,
			dataType: "json",
			async: false,
			success: function (data) {
				var list = data.data;
				$("select[name='" + node + "']").html('');
				var option = document.createElement("option");
				option.setAttribute("value", '');
				option.innerText = '请选择';
				$("select[name='" + node + "']").append(option);
				if (list != null || list.size() > 0) {
					for (var c in list) {
						var option = document.createElement("option");
						option.setAttribute("value", c);
						option.innerText = list[c];
						$("select[name='" + node + "']").append(option)
					}
				};

			}
		});
	}
});



