
//CODIGO DE FOTO de registro_producto.php
$(document).ready(function(){
    // dar movimeinto al menu bars
    $('.btnMenu').click(function(e){
        e.preventDefault();

        if($('nav').hasClass('viewMenu')){
            $('nav').removeClass('viewMenu');
        }else{
            $('nav').addClass('viewMenu');
        }
    });

    // Sub menu mover
    $('nav ul li').click(function () { 
        $('nav ul li ul').slideUp();
        $(this).children('ul').slideToggle();
        
    });

    //--------------------- SELECCIONAR FOTO PRODUCTO ---------------------
    $("#foto").on("change",function(){
    	var uploadFoto = document.getElementById("foto").value;
        var foto       = document.getElementById("foto").files;
        var nav = window.URL || window.webkitURL;
        var contactAlert = document.getElementById('form_alert');
        
            if(uploadFoto !='')
            {
                var type = foto[0].type;
                var name = foto[0].name;
                if(type != 'image/jpeg' && type != 'image/jpg' && type != 'image/png')
                {
                    contactAlert.innerHTML = '<p class="errorArchivo">El archivo no es válido.</p>';                        
                    $("#img").remove();
                    $(".delPhoto").addClass('notBlock');
                    $('#foto').val('');
                    return false;
                }else{  
                        contactAlert.innerHTML='';
                        $("#img").remove();
                        $(".delPhoto").removeClass('notBlock');
                        var objeto_url = nav.createObjectURL(this.files[0]);
                        $(".prevPhoto").append("<img id='img' src="+objeto_url+">");
                        $(".upimg label").remove();
                        
                    }
              }else{
              	alert("No selecciono foto");
                $("#img").remove();
              }              
    });

    $('.delPhoto').click(function(){
    	$('#foto').val('');
    	$(".delPhoto").addClass('notBlock');
    	$("#img").remove();

        //VAlidacion para remover foto poner nombre determinado sin foto img_producto.png
        if($("#foto_actual") && $("#foto_remove")){
            $("#foto_remove").val('img_producto.png');
        }

    });


    //Modal Form add PRoduct Agregar Producto
    
    //1.Dar un evento a una class
    $('.add_product').click(function(e){
        
        e.preventDefault();
        var producto = $(this).attr('product');
        var action = 'infoProducto';

        $.ajax({
            url:'ajax.php',
            type: 'POST',
            async: true,
            data:{action:action,producto:producto},

            success: function(response){
                if(response != 'error'){

                    var info = JSON.parse(response);
                   // var info = JSON.parse(JSON.stringify(response));;
                  

                   // $('#producto_id').val(info.codproducto);
                   // $('.nameProducto').html(info.descripcion);

                    $('.bodyModal').html('<form action="" method="POST" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); sendDataProduct();"> '+

                     '<h1><i class="fas fa-cubes" style="font-size: 45pt;"></i><br> Agregar Producto</i></h1>'+
                   
                     '<h2 class="nameProducto">'+info.descripcion+'</h2><br>'+
                   
                     '<input type="number" name="cantidad" id="txtCantidad" placeholder="Cantidad del Producto" required><br>'+
                     '<input type="text" name="precio" id="txtPrecio" placeholder="Precio del producto" required>'+
                     
                     '<input type="hidden" name="producto_id" id="producto_id" value="'+info.codproducto+'" required>'+
                     '<input type="hidden" name="action" value="addProduct" required>'+
                
                     '<div class="alert alertAddProduct"></div>	'+
                   
                     '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Agregar</button>'+
                     '<a href="#" class="btn_ok closeModal" onclick="coloseModal();"><i class="fas fa-ban"></i> Cerrar</a>'+
     
                    '</form>');
                }
            },
            error: function(error){
                console.log(error);
            }
        })

        $('.modal').fadeIn();
      
    });

    //Modal form Delete Product   
    //2.Eliminar Producto delete_product
    $('.del_product').click(function(e){
        
        e.preventDefault();
        var producto = $(this).attr('product');
        var action = 'infoProducto';

        $.ajax({
            url:'ajax.php',
            type: 'POST',
            async: true,
            data:{action:action,producto:producto},

            success: function(response){
                if(response != 'error'){

                    var info = JSON.parse(response);
                   // var info = JSON.parse(JSON.stringify(response));;
                  

                   // $('#producto_id').val(info.codproducto);
                   // $('.nameProducto').html(info.descripcion);

                    $('.bodyModal').html('<form action="" method="POST" name="form_del_product" id="form_del_product" onsubmit="event.preventDefault(); delProduct();"> '+

                     '<h1><i class="fas fa-cubes" style="font-size: 45pt;"></i><br> Eliminar Producto</i></h1>'+
                    
                     '<p>¿Esta seguro de eliminar el siguiente Producto?</p>'+
                  

                     '<h2 class="nameProducto">'+info.descripcion+'</h2><br>'+
                   
                  
                     
                     '<input type="hidden" name="producto_id" id="producto_id" value="'+info.codproducto+'" required>'+
                     '<input type="hidden" name="action" value="delProduct" required>'+
                
                     '<div class="alert alertAddProduct"></div>	'+

                     '<a href="#" class="btn_cancel"  onclick="coloseModal();"><i class="fas fa-ban"></i> Cerrar</a>'+
                     '<button type="submit" class="btn_ok"><i class="far fa-trash-alt"></i> Eliminar</button>'+
     
                    '</form>');
                }
            },
            error: function(error){
                console.log(error);
            }
        })

        $('.modal').fadeIn();
      
    });


    //Buscar proveedor
    $('#search_proveedor').change(function(e){
        e.preventDefault();

        var sistema = getUrl();
      
        location.href = sistema+'buscar_productos.php?proveedor='+$(this).val();
        //alert(URLactual)
    });

    // Activa campos para registra Clientes
    $('.btn_new_cliente').click(function(e){
        e.preventDefault();
        $('#nom_cliente').removeAttr('disabled');
        $('#tel_cliente').removeAttr('disabled');
        $('#dir_cliente').removeAttr('disabled');

        $('#div_registro_cliente').slideDown();
    })


    // Buscar Cliente
    $('#dni_cliente').keyup(function (e) { 
        e.preventDefault();
        var cl = $(this).val();
        var action = 'searchCliente';
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: {action:action,cliente:cl},
            success: function (response) {
               
                if(response == 0){
                    $('#idcliente').val('');
                    $('#nom_cliente').val('');
                    $('#tel_cliente').val('');
                    $('#dir_cliente').val('');
                    // Mostrar boton agregar    
                    $('.btn_new_cliente').slideDown();
                }else{
                    var data = $.parseJSON(response);
                    $('#idcliente').val(data.idcliente);
                    $('#nom_cliente').val(data.nombre);
                    $('#tel_cliente').val(data.telefono);
                    $('#dir_cliente').val(data.direccion);
                    // Ocultar boton agregar
                    $('.btn_new_cliente').slideUp();

                    // Bloqueo de campos
                    $('#nom_cliente').attr('disabled','disabled');
                    $('#tel_cliente').attr('disabled','disabled');
                    $('#dir_cliente').attr('disabled','disabled');

                    // Ocultar boton Guardar
                    $('#div_registro_cliente').slideUp();
                }
            },
            error: function (error) {

              }
        });
    });


    // Crear Cliente - Ventas
    $('#form_new_cliente_venta').submit(function (e) { 
        e.preventDefault();
        
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: $('#form_new_cliente_venta').serialize(),
            success: function (response) {
               
                if(response != '\r\nerror'){
                    // Agregar id a input hidden
                    $('#idcliente').val(response);
                    // Bloqueo campos
                    $('#nom_cliente').attr('disabled','disabled');
                    $('#tel_cliente').attr('disabled','disabled');
                    $('#dir_cliente').attr('disabled','disabled');

                    // Ocultar   boton agregar
                    $('.btn_new_cliente').slideUp();
                    // Ocultar boton guardar
                    $('#div_registro_cliente').slideUp();
                }
               
            },
            error: function (error) {

              }
        });
    });
    
    // Buscar Producto
    $('#txt_cod_producto').keyup(function (e) { 
        e.preventDefault();

        var producto = $(this).val();
        var action = 'infoProducto';
        
        if(producto != ''){
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: {action:action,producto:producto},
            success: function (response) {
               
               if(response != '\r\nerror'){
                   var info = JSON.parse(response);
                   $('#txt_descripcion').html(info.descripcion);
                   $('#txt_existencia').html(info.existencia);
                   $('#txt_cant_producto').val('1');
                   $('#txt_precio').html(info.precio);
                   $('#txt_precio_total').html(info.precio);

                    // Activar cantidad
                    $('#txt_cant_producto').removeAttr('disabled');

                    // Mostrar boton agregar
                    $('#add_product_venta').slideDown();
               }else{
                   $('#txt_descripcion').html('-');
                   $('#txt_existencia').html('-');
                   $('#txt_cant_producto').val('0');
                   $('#txt_precio').val('0.00');
                   $('#txt_precio_total').html('0.00');

                    // Bloquear Cantidad
                    $('#txt_cant_producto').attr('disabled','disabled');

                    // Ocultar boton agregar
                    $('#add_product_venta').slideUp();
               }
               
            },
            error: function (error) {

              }
        });
    }
    });

    // Validar cantidad del producto antes de agregar
    $('#txt_cant_producto').keyup(function (e) {
        e.preventDefault();

        var precio_total = $(this).val() * $('#txt_precio').html();
        var existencia = parseInt($('#txt_existencia').html());
        $('#txt_precio_total').html(precio_total);

        // Ocultar el boton agregar si la cantidad es menor que 1

        if( ($(this).val() < 1 || isNaN($(this).val())) || ($(this).val() > existencia) ){
            $('#add_product_venta').slideUp();
        }else{
            $('#add_product_venta').slideDown();
        }
      });

        // Agregar producto al detalle
        $('#add_product_venta').click(function (e) {
           e.preventDefault();
           if($('#txt_cant_producto').val() > 0){
               var codproducto = $('#txt_cod_producto').val();
               var cantidad = $('#txt_cant_producto').val();
               var action = 'addProductoDetalle';
               $.ajax({
                   url: 'ajax.php',
                   type: "POST",
                   async: true,
                   data: {action:action,producto:codproducto,cantidad:cantidad},
                   
                   success: function (response) {
                        if(response != '\r\nerror'){

                            var info = JSON.parse(response);
                            
                            $('#detalle_venta').html(info.detalle);
                            $('#detalle_totales').html(info.totales); 

                            $('#txt_cod_producto').val('');
                            $('#txt_descripcion').html('-');
                            $('#txt_existencia').html('-');
                            $('#txt_cant_producto').val('0');
                            $('#txt_precio').html('0.00');
                            $('#txt_precio_total').html('0.00');


                            // Bloquear cantidad
                            $('#txt_cant_producto').attr('disabled','disabled');

                            // Ocultar boton agregar
                            $('#add_product_venta').slideUp();
                        } else{
                            console.log('no data');
                        }

                        viewProcesar();
                   },
                   error: function(error){
                       
                   }
               });
           } 
        });



        // Anular Venta

        $('#btn_anular_venta').click(function(e){
            e.preventDefault();

            var rows = $('#detalle_venta tr').length;

            if(rows > 0){
                var action = 'anularVenta';

                $.ajax({
                    url: 'ajax.php',
                    type: "POST",
                    async: true,
                    data:{action:action},

                    success: function(response){

                        

                        if(response != '\r\nerror'){
                            location.reload();
                        }
                    },
                    error: function(error){

                    }
                })
            }

        }); 


        // Facturar Venta
        $('#btn_facturar_venta').click(function(e){
            e.preventDefault();

            var rows = $('#detalle_venta tr').length;

            if(rows > 0){
                var action = 'procesarVenta';
                var codcliente = $('#idcliente').val();

                $.ajax({
                    url: 'ajax.php',
                    type: "POST",
                    async: true,
                    data:{action:action,codcliente:codcliente},

                    success: function(response){

                        // console.log(response);
                        

                        if(response != '\r\nerror'){

                            var info = JSON.parse(response);
                            // console.log(info);

                            generarPDF(info.codcliente,info.nofactura);

                            location.reload();
                        }else{
                            console.log('no data');
                        }

                    },
                    error: function(error){

                    }
                })
            }

        }); 

        // Modal Form Anular Factura
       
        $('.anular_factura').click(function(e){
        
            e.preventDefault();
            
            var nofactura = $(this).attr('fac');
            var action = 'infoFactura';
    
            $.ajax({
                url:'ajax.php',
                type: "POST",
                async: true,
                data:{action:action,nofactura:nofactura},
    
                success: function(response){
                    if(response != '\r\nerror'){
    
                        var info = JSON.parse(response);

                        // console.log(info);
                       // var info = JSON.parse(JSON.stringify(response));;
                      
    
                       // $('#producto_id').val(info.codproducto);
                       // $('.nameProducto').html(info.descripcion);
    
                        $('.bodyModal').html('<form action="" method="POST" name="form_anular_factura" id="form_anular_factura" onsubmit="event.preventDefault(); anularFactura();"> '+
    
                         '<h1><i class="fas fa-cubes" style="font-size: 45pt;"></i><br> Anular Venta</i><br></h1>'+
                        
                         '<p>¿Realmente Desea Anular la Venta?</p>'+
                      
                        '<p><strong>No. '+info.nofactura+'</strong></p>'+
                        '<p><strong>Monto. S/. '+info.totalfactura+'</strong></p>'+
                        '<p><strong>Fecha. '+info.fecha+'</strong></p>'+
                        '<input type="hidden" name="action" value="anularFactura">'+
                        '<input type="hidden" name="no_factura" id="no_factura" value="'+info.nofactura+'" required>'+
                    
                         '<div class="alert alertAddProduct"></div>	'+
    
                         '<button type="submit" class="btn_ok"><i class="far fa-trash-alt"></i> Anular</button>'+
                         '<a href="#" class="btn_cancel"  onclick="coloseModal();"><i class="fas fa-ban"></i> Cerrar</a>'+
         
                        '</form>');
                    }
                },
                error: function(error){
                    console.log(error);
                }
            })
    
            $('.modal').fadeIn();
          
        });

        // Ver factura
        $('.view_factura').click(function (e) { 
            e.preventDefault();
            
            // Capturar datos codgo client ey cod factura
            var codCliente = $(this).attr('cl');
            var noFactura = $(this).attr('f');
            // Llamar funcion
            generarPDF(codCliente,noFactura);

        });

        // Cambiar password
        $('.newPass').keyup(function () { 
            validPass();
        });

        // Form Cambiar contraseña
        $('#frmChangePass').submit(function (e) { 
            e.preventDefault();
            
            var passActual = $('#txtPassUser').val();
            var passNuevo = $('#txtNewPassUser').val();
            var confirmPassNuevo = $('#txtPassConfirm').val();
            var action = "changePassword";

            if(passNuevo != confirmPassNuevo){
                $('.alertChangesPass').html('<p style="color:red;">Las Contraseñas no son Iguales.</p>');
                $('.alertChangesPass').slideDown();
                return false;
            }
    
            // Validar de mas caracteres
            if(passNuevo.length < 6){
                $('.alertChangesPass').html('<p style="color:red;">Las nueva contraseña debe ser de 6 caracteres como mínimo.</p>');
                $('.alertChangesPass').slideDown();
                return false;
            }

            $.ajax({
                url: 'ajax.php',
                type: "POST",
                async: true,
                data:{action:action,passActual:passActual,passNuevo:passNuevo},

                success: function(response){

                    if(response != '\r\nerror'){
                        var info = JSON.parse(response);
                        
                        if(info.cod == '00'){
                            $('.alertChangesPass').html('<p style="color:green;">'+info.msg+'</p>');
                            $('#frmChangePass')[0].reset();
                        }else{
                            $('.alertChangesPass').html('<p style="color:red;">'+info.msg+'</p>');
                        }

                        $('.alertChangesPass').slideDown();
                    }
                    

           
                    

                },
                error: function(error){

                }
            })
        });


        // Actualiza datos Empresa
        $('#frmEmpresa').submit(function (e) { 
            e.preventDefault();

            var intRuc = $('#txtRuc').val();
            var strNombreEmp = $('#txtNombre').val();
            var strRSocialEmp = $('#txtRSocial').val();
            var intTelEmp = $('#txtTelEmpresa').val();
            var strEmailEmp = $('#txtEmailEmpresa').val();
            var strDirEmp = $('#txtDirEmpresa').val();
            var intIgv = $('#txtIgv').val();

            if( intRuc == '' || strNombreEmp == '' || intTelEmp == '' || strEmailEmp == '' || strDirEmp =='' || intIgv == ''){
                $('.alertFormEmpresa').html('<p style="color:red">Todos los Campos son obligatorio.</p>');
                $('.alertFormEmpresa').slideDown();
                return false;
            }

            $.ajax({
                url: 'ajax.php',
                type: "POST",
                async: true,
                data: $('#frmEmpresa').serialize(),
                beforeSend: function(){
                    $('.alertFormEmpresa').slideUp();
                    $('.alertFormEmpresa').html();
                    $('#frmEmpresa input').attr('disabled','disabled');
                },
                success: function (response) {
                    console.log(response);
                    var info = JSON.parse(response);
                    if(info.cod == '00'){
                        $('.alertFormEmpresa').html('<p style="color: #23922d;">'+info.msg+'</p>');
                        $('#frmEmpresa input').removeAttr('disabled');
                        $('.alertFormEmpresa').slideDown();
                    }else{
                        $('.alertFormEmpresa').html('<p style="color:red;">'+info.msg+'</p>');
                }

                    $('.alertFormEmpresa').slideDown();
                    $('#frmEmpresa input').removeAttr('disabled');
                    
                },
                error: function(error){

                }
            });
            
        });

}); //End Ready

    // Validar Contraseña
    function validPass(){
        // Funcion para validar contrsalsea
        var passNuevo = $('#txtNewPassUser').val();
        var confirmPassNuevo = $('#txtPassConfirm').val();

        if(passNuevo != confirmPassNuevo){
            $('.alertChangesPass').html('<p style="color:red;">Las Contraseñas no son Iguales.</p>');
            $('.alertChangesPass').slideDown();
            return false;
        }

        // Validar de mas caracteres
        if(passNuevo.length < 6){
            $('.alertChangesPass').html('<p style="color:red;">Las nueva contraseña debe ser de 6 caracteres como mínimo.</p>');
            $('.alertChangesPass').slideDown();
            return false;
        }

        // 
        $('.alertChangesPass').html('');
        $('.alertChangesPass').slideUp();
    }
    // Anular factura
    function anularFactura(){
        var noFactura = $('#no_factura').val();
        var action = 'anularFactura';
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: {action:action,noFactura:noFactura},
            success: function (response) {
                if(response == '\r\nerror'){
                    $('.alertAddProduct').html('<p style="color:red;">Error al Anular Venta.</p>');
                }else{
                    // Procesos para eliminar algunos botones 
                    $('#row_'+noFactura+' .estado').html('<span class="anulada">Anulada</span>');
                    $('#form_anular_factura .btn_ok').remove();
                    $('#row_'+noFactura+' .div_factura').html('<button type="button" class="btn_anular inactive"><i class="fas fa-ban"></i></button>');
                    $('.alertAddProduct').html('<p>Venta Anulada.</p>');
                }
            },
            error: function(error){

            }
        });
    }

    //Genrare facura 
    function generarPDF(cliente,factura){
        var ancho = 1000;
        var alto = 800;

        // Calcular posicion x y para centrar ventana
        var x = parseInt((window.screen.width/2) - (ancho / 2));
        var y = parseInt((window.screen.height/2) - (alto / 2));

        $url = 'factura/generaFactura.php?cl='+cliente+'&f='+factura;
        window.open($url,"Factura","left="+x+",top="+y+",height="+alto+",width="+ancho+",scrollbar=si,location=no,resizable=si,menubar=no");
    }


    function del_product_detalle(correlativo){
        var action = 'delProductoDetalle';
        var id_detalle = correlativo;
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: {action:action,id_detalle:id_detalle},
            
            success: function (response) {
                 if(response != '\r\nerror'){
                    var info = JSON.parse(response);

                    $('#detalle_venta').html(info.detalle);
                    $('#detalle_totales').html(info.totales); 


                    $('#txt_cod_producto').val('');
                    $('#txt_descripcion').html('-');
                    $('#txt_existencia').html('-');
                    $('#txt_cant_producto').val('0');
                    $('#txt_precio').html('0.00');
                    $('#txt_precio_total').html('0.00');


                    // Bloquear cantidad
                    $('#txt_cant_producto').attr('disabled','disabled');

                     // Ocultar boton agregar
                    $('#add_product_venta').slideUp();
                 }else{
                     $('#detalle_venta').html('');
                     $('#detalle_totales').html('');
                 }
                 viewProcesar();
              
            },
            error: function(error){
                
            }
        });       
    }


    // Ocultar y mostrar boton procesar
    function viewProcesar(){
        if($('#detalle_venta tr').length > 0){
            $('#btn_facturar_venta').show();
        }else{
            $('#btn_facturar_venta').hide();
        }
    }


    function serchForDetalle(id){
        var action = 'serchForDetalle';
        var user = id;
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: {action:action,user:user},
            
            success: function (response) {
                // console.log(response);
                if(response != '\r\nerror'){

                    var info = JSON.parse(response);
                    
                    $('#detalle_venta').html(info.detalle);
                    $('#detalle_totales').html(info.totales); 

                } else{
                    console.log('no data');
                }
                viewProcesar();
            },
            error: function(error){
                
            }
        });
    }

    function getUrl(){
        
        var loc = window.location;
        var pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf('/') + 1);
        return loc.href.substring(0, loc.href.length - ((loc.pathname + loc.search + loc.hash).length - pathName.length));

    }

    function sendDataProduct(){

        $('.alertAddProduct').html('');

        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            async: true,
            data: $('#form_add_product').serialize(),

            success: function(response){
                if(response == 'error'){
                    $('.alertAddProduct').html('<p style="color: red;">Error al Agregar Producto</p>');
                }else{
                    var info = JSON.parse(response);

                    
                    $('.row'+info.producto_id +' .celPrecio').html(info.nuevo_precio);
                    $('.row'+info.producto_id +' .celExistencia').html(info.nueva_existencia);

                    /*
                    $('.row'+info.producto_id +' .celPrecio').html(info.nuevo_precio);
                    $('.row'+info.producto_id +' .celExistencia').html(info.nueva_existencia);
                    */

                    $('#txtCantidad').val('');
                    $('#txtPrecio').val('');
                    $('.alertAddProduct').html('<p>Producto Guardado Correctamente</p>');
                }   
            },
            error: function(error){
                console.log(error);
            }
        });

    }


    //Eliminar Producto
    function delProduct(){

        var pr = $('#producto_id').val();
        $('.alertAddProduct').html('');

        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            async: true,
            data: $('#form_del_product').serialize(),

            success: function(response){
                
                console.log(response);
          
                if(response == 'error'){
                    $('.alertAddProduct').html('<p style="color: red;">Error al Eliminar el Producto</p>');
                }else{

                    $('.row'+pr).remove();
                    $('#form_del_product .btn_ok').remove();

                    $('.alertAddProduct').html('<p>Producto Eliminado Correctamente</p>');
                }  
            },
            error: function(error){
                console.log(error);
            }
        });

    }




//Accion de Eliminar Prodcuto



//***********Accion de cerrar Prodcuto****/ */

function coloseModal(){

    $('.alertAddProduct').html('');
    //Limpiar numeros luego de ser ingresado
    $('#txtCantidad').val('');
    $('#txtPrecio').val('');

    $('.modal').fadeOut();
}


