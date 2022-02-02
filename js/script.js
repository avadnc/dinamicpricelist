$("#loading").hide();
var tabla = $("#tabla").DataTable({
	searching: true,
	keys: true,
	responsive: true,
	buttons: ["copy", "csv", "excel", "pdf", "print"],
	lengthMenu: [
		[10, 25, 50, -1],
		[10, 25, 50, "All"],
	],
	columnDefs: [{ targets: "_all", width: "auto" }],
	dom: "lBfrtip",
	language: {
		sProcessing: "Procesando",
		sLengthMenu: "Mostrar _MENU_ registros",
		zeroRecords: "No se encontraron registros",
		sEmptyTable: "Ningún dato disponible en esta tabla",
		sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
		sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0",
		sInfoFiltered: "(filtrado un total de _MAX_ registros)",
		sInfoPostFix: "",
		sSearch: "Buscar:",
		sUrl: "",
		sInfoThousands: ",",
		sLoadingRecords: "Cargando...",
		oPaginate: {
			sFirst: "Primero",
			sLast: "Último",
			sNext: "Siguiente",
			sPrevious: "Anterior",
		},
		oAria: {
			sSortAscending: ": Activar para odernar la columna de manera ascendente",
			sSortDescending: "Activar para ordenar la columna de manera descendente",
		},
	},
});
var tablaupdate = $("#tablaupdate").DataTable({
	searching: true,
	keys: true,
	responsive: true,
	//buttons: ["copy", "csv", "excel", "pdf", "print"],
	lengthMenu: [
		[10, 25, 50, -1],
		[10, 25, 50, "All"],
	],
	columnDefs: [{ targets: "_all", width: "auto" }],
	dom: "lBfrtip",
	language: {
		sProcessing: "Procesando",
		sLengthMenu: "Mostrar _MENU_ registros",
		zeroRecords: "No se encontraron registros",
		sEmptyTable: "Ningún dato disponible en esta tabla",
		sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
		sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0",
		sInfoFiltered: "(filtrado un total de _MAX_ registros)",
		sInfoPostFix: "",
		sSearch: "Buscar:",
		sUrl: "",
		sInfoThousands: ",",
		sLoadingRecords: "Cargando...",
		oPaginate: {
			sFirst: "Primero",
			sLast: "Último",
			sNext: "Siguiente",
			sPrevious: "Anterior",
		},
		oAria: {
			sSortAscending: ": Activar para odernar la columna de manera ascendente",
			sSortDescending: "Activar para ordenar la columna de manera descendente",
		},
	},
});
$("#tablaupdate_filter").hide();
$("#tabla_filter").hide();
$("#tablaupdate_length").hide();
$("#tabla_length").hide();

$("#search_button").click(function (e) {
	e.preventDefault();
	tabla.clear().draw();
	category = document.getElementById("category").value;
	ref = document.getElementById("ref").value;
	data = new FormData();
	data.append("category", category);
	data.append("ref", ref);
	data.append("action", "getproducts");

	$.ajax({
		url: window.location.href,
		method: "POST",
		contentType: false,
		processData: false,
		data: data,
		beforeSend: function () {
			$("#loading").show();
		},
		success: function (resp) {
			result = JSON.parse(resp);
			var count = result.length;

			for (var i = 0; i < count; i++) {
				if (!$.trim(result[i]["stock_reel"])) {
					stock = "<strong style='color:red;'>Sin Stock</strong>";
				} else {
					if (result[i]["stock_reel"] != 0) {
						stock = result[i]["stock_reel"];
					} else {
						stock = "<strong style='color:red;'>Sin Stock</strong>";
					}
				}

				if (!$.trim(result[i]["substitution"])) {
					substitution = '<strong style="color:red;">N/D</strong>';
				} else {
					substitution = result[i]["substitution"];
				}

				var row = [result[i]["ref"], substitution, result[i]["label"], stock];

				$.each(result[i]["currency"], function (key, value) {
					$.each(value, function (key, value) {
						if (value == 0) {
							row.push("<strong style='color:red;'>N/D</strong>");
						} else {
							row.push("$ " + numberWithCommas(value) + " " + key);
						}
					});
				});
				row.push(result[i]["date_price"]);
				$.fn.DataTable({
					pageLength: 100,
					lengthMenu: [
						[10, 20, 25, 50, -1],
						[10, 20, 25, 50, "All"],
					],
				});
				tabla.row.add(row).draw(false);
			}
		},
		complete: function () {
			$("#loading").hide();
			$("#tabla_filter").show();
			$("#tabla_length").show();
		},
	});
});
$("#search_button_update").click(function (e) {
	e.preventDefault();
	tablaupdate.clear().draw();
	category = document.getElementById("category").value;
	ref = document.getElementById("ref").value;
	supid = document.getElementById("supid").value;
	data = new FormData();
	data.append("category", category);
	data.append("ref", ref);
	data.append("supid", supid);

	data.append("action", "getproducts");

	$.ajax({
		url: window.location.href,
		method: "POST",
		contentType: false,
		processData: false,
		data: data,
		cache: false,
		timeout: 500000,
		beforeSend: function () {
			$("#loading").show();
		},
		success: function (resp) {
			result = JSON.parse(resp);
			var count = result.length;
			for (var i = 0; i < count; i++) {
				let id = result[i]["id"];
				let profit = 0;
				var cost_price = 0;
				var currency = "";
				if (result[i]["supplier"] != undefined) {
					var date =
						"<span id='date" +
						result[i]["id"] +
						"'>" +
						result[i]["supplier"][0]["modification_date"] +
						"</span>";
				} else {
					date = "";
				}
				var proveedor =
					"<a class='butAction' target='_blank' href='../../product/fournisseurs.php?id=" + //poner la url absoluta
					result[i]["id"] +
					"'>Agregar Proveedor</a>";
				if (result[i]["supplier"] != null) {
					if (result[i]["supplier"].length > 0) {
						cost_price = result[i]["supplier"][0]["price"];
						currency = result[i]["supplier"][0]["currency"];

						// console.log(result[i]["supplier"][0]["profit"]);
						profit = result[i]["supplier"][0]["profit"];
						var proveedor =
							'<select name="suplist"  idprod="' +
							result[i]["id"] +
							'" id="suplist' +
							result[i]["id"] +
							'">';

						$.each(result[i]["supplier"], function (key, value) {
							proveedor =
								proveedor +
								'<option value="' +
								value.supid +
								'">' +
								value.name +
								"</option>";
						});
						proveedor = proveedor + "</select>";
					}
				} else if (result[i]["price"] > 0) {
					cost_price = result[i]["price"];
					currency = "";
				} else {
					cost_price = 0;
					currency = "";
				}

				// console.log(currency);return;
				inputprice =
					'<input style="width:60px;margin-right:1rem;" type="text" class="editcost" id="' +
					result[i]["id"] +
					'" value="' +
					cost_price +
					'"  idprod="' +
					result[i]["id"] +
					'">' +
					"<span id='curr" +
					result[i]["id"] +
					"'>" +
					currency +
					"</span>";
				inputmargen =
					'<input style="width:60px" class="editmarg" margen="' +
					result[i]["id"] +
					'" type="text"  idprod="' +
					result[i]["id"] +
					'" id="margin' +
					result[i]["id"] +
					'" value="' +
					profit +
					'">%';

				var row = [
					result[i]["ref"],
					result[i]["label"],
					inputprice,
					proveedor,
					date,
					inputmargen,
				];

				$.each(result[i]["currency"], function (key, value) {
					$.each(value, function (key, value) {
						if (value == 0) {
							row.push(
								'<input style="width:60px" class="edit' +
									key +
									'" type="text" id="idprod' +
									result[i]["id"] +
									'" currency="' +
									key +
									'" ' +
									key +
									'="' +
									result[i]["id"] +
									'" value="0"  idprod="' +
									result[i]["id"] +
									'">' +
									key
							);
						} else {
							row.push(
								'<input style="width:60px" class="edit' +
									key +
									'" type="text" id="idprod' +
									result[i]["id"] +
									'" currency="' +
									key +
									'" ' +
									key +
									'="' +
									result[i]["id"] +
									'" value="' +
									numberWithCommas(value) +
									'"  idprod="' +
									result[i]["id"] +
									'">' +
									key
							);
						}
					});
				});

				$.fn.DataTable({
					pageLength: 100,
					lengthMenu: [
						[10, 20, 25, 50, -1],
						[10, 20, 25, 50, "All"],
					],
				});
				tablaupdate.row.add(row).draw(false);
			}
		},
		complete: function () {
			$("#loading").hide();
			$("#tablaupdate_filter").show();
			$("#tablaupdate_length").show();
		},
	});
});
$("#ref").on("input", function () {
	if ($("#ref").length && $("#ref").val().length) {
		$("#category").empty();
		$("#category").prop("disabled", true);
		$("#category").prop("selected", false);
	} else {
		$("#category").prop("disabled", false);
	}
});

function numberWithCommas(x) {
	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
function getProductPrice(id = "", supid = "") {
	data = new FormData();
	data.append("category", category);
	data.append("id", id);
	data.append("supid", supid);
	data.append("action", "getpricesupplier");

	response = $.ajax({
		url: window.location.href,
		method: "POST",
		contentType: false,
		processData: false,
		data: data,
		async: false,
		cache: false,
		success: function (data) {
			return data;
		},
	}).responseText;
	return response;
}
function showProductPrice(data) {
	result = data;
	return result;
}
function getMarginProfit(cost, price, currency = null) {
	if (cost == null || price == null) return;
	var cost = cost.toString().replace(",", "");
	var price = price.toString().replace(",", "");
	if (cost == 0 && price == 0) {
		return 0;
	} else if (cost == 0) {
		return 0;
	} else {
		if (currency != null) {
			var currency = price.toString().replace(",", "");
			parseFloat(currency);
			parseFloat(price);
			parseFloat(cost);
			result0 = cost / currency;
			result1 = price / result0;
			result2 = result1 * 100;
			result3 = result2 - 100;
			return result3.toFixed(2);
		} else {
			parseFloat(price);
			parseFloat(cost);
			result1 = price / cost;
			result2 = result1 * 100;
			result3 = result2 - 100;

			return result3.toFixed(2);
		}
	}
}
tablaupdate.on("key", function (e, datatable, key, cell, originalEvent) {
	if (key == 13) {
		textoseparado = cell.data().split('"');

		if (textoseparado[3] == "editcost") {
			$("#" + textoseparado[5]).focus();
			$("#" + textoseparado[5]).select();
		}
		if (textoseparado[3] == "editmarg") {
			$('input[margen="' + textoseparado[5] + '"]').focus();
			$('input[margen="' + textoseparado[5] + '"]').select();
		}
		if (textoseparado[3] == "editUSD") {
			$('input[USD="' + textoseparado[11] + '"]').focus();
			$('input[USD="' + textoseparado[11] + '"]').select();
		}
		if (textoseparado[3] == "editMXN") {
			$('input[MXN="' + textoseparado[11] + '"]').focus();
			$('input[MXN="' + textoseparado[11] + '"]').select();
		}
	}
});

$(document).on("keypress", ".editmarg", function (e) {
	if (e.which == 13) {
		let element = $(this)[0];

		margin = $(element).val();

		let idcompra = $(element).attr("idprod");
		supid = $("#suplist" + idcompra)
			.prop("selected", true)
			.val();

		cost_price = $("#" + idcompra).val();

		price =
			(parseFloat(cost_price) * parseFloat(margin)) / 100 +
			parseFloat(cost_price);
		currency = $("#curr" + idcompra).html();

		if (typeof currency !== "undefined") {
			currency = $("#localcurrency").val();
		}
		
		data = new FormData();
		data.append("id", idcompra);
		data.append("cost_price", cost_price);
		data.append("currency", currency);
		data.append("margin", margin);
		data.append("supid", supid);
		data.append("action", "update");

		$.ajax({
			method: "POST",
			contentType: false,
			processData: false,
			data: data,
			async: false,
			cache: false,
			success: function (result) {
				result = JSON.parse(result);
			
				if (result[0] == "err") {
					alert(result[1] + "%");
					return;
				}
				$.each(result["currency"], function (key, value) {
					$.each(value, function (key, value) {
						if (value == 0) {
							$("input[" + key + "='" + idcompra + "']").val(0);
							$("input[" + key + "='" + idcompra + "']")
								.parent()

								.css("background-color", "#008000");
						} else {
							$("input[" + key + "='" + idcompra + "']").val(
								numberWithCommas(value)
							);
							$("input[" + key + "='" + idcompra + "']")
								.parent()

								.css("background-color", "#008000");
						}
					});
				});
			},
		});
	}
});

$("input[name*='currency']").each(function (key, value) {
	let idcurr = $(value).attr("id");
	let exchage = $(value).val();
	let localcurrency = $("#localcurrency").val();

	$(document).on("keypress", ".edit" + idcurr, function (e) {
		if (e.which == 13) {
			let element = $(this)[0];
			if (typeof margin !== "undefined") {
				delete margin;
			}
			if (typeof price !== "undefined") {
				delete price;
			}

			price = $(element).val();
			let idcompra = $(element).attr("idprod");
			cost_price = $("#" + idcompra).val();
			currency = $("#curr" + idcompra).html();

			if (currency != localcurrency) {
				// cost_price = parseFloat(cost_price) / parseFloat(exchage);
				price = parseFloat(price) / parseFloat(exchage);
			}
			supid = $("#suplist" + idcompra)
				.prop("selected", true)
				.val();
			data = new FormData();
			data.append("id", idcompra);
			data.append("price", price);
			data.append("supid", supid);
			data.append("currency", currency);
			data.append("cost_price", cost_price);
			data.append("action", "update");

			$.ajax({
				method: "POST",
				contentType: false,
				processData: false,
				data: data,
				async: false,
				cache: false,
				success: function (result) {
					result = JSON.parse(result);
					if (result[0] == "err") {
						alert(result[1] + "%");
						return;
					}
					$.each(result["currency"], function (key, value) {
						$.each(value, function (key, value) {
							if (value == 0) {
								$("input[" + key + "='" + idcompra + "']").val(0);
								$("input[" + key + "='" + idcompra + "']")
									.parent()

									.css("background-color", "#008000");
							} else {
								$("input[" + key + "='" + idcompra + "']").val(
									numberWithCommas(value)
								);
								$("input[" + key + "='" + idcompra + "']")
									.parent()

									.css("background-color", "#008000");
							}
						});
					});
					marginprofit = result["supplier"][0]["profit"];
					$("#margin" + idcompra).val(marginprofit);
				},
			});
		}
	});
});

$(document).on("change click", "select[name='suplist']", function () {
	let element = $(this)[0];
	prov = $(element).prop("selected", true).val();
	idprod = $(element).attr("idprod");
	data = getProductPrice(idprod, prov);
	data = JSON.parse(data);
	$("#" + idprod)
		.next("span")
		.html(data[0]["currency"]);

	$("#" + idprod).val(data[0]["price"]);
	$("#margin" + idprod).val(data[0]["profit"]);
	$("#date" + idprod).html("<span>" + data[0]["modification_date"] + "</span>");
});
