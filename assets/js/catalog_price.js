var parent_add;
var next_num;
function getContactorList()
{
    var api = '';
    $.get('api.php?act=catalog_contractor_list_get')
    .done(function(data) {api = data;})
    .fail(function() {return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {return false;}
        if(api.data.length < 1)
        {
            $('select[name="contactor"]').html('<option value="0">Подрядчиков не обнаружено</option>'); 
            $('select[name="customer"]').html('<option value="0">Подрядчиков не обнаружено</option>'); 
            return false;}
        var item = '';
        $('select[name="contactor"]').html('');
        $('select[name="customer"]').html('');
        for(var i=0;i<api.data.length;i++)
        {
            item = '<option value="'+api.data[i].id+'">'+api.data[i].name+'</option>';
            $('select[name="contactor"]').append(item);
            $('select[name="customer"]').append(item);
        }
    });
}
$(document).ready(function(){
    getPriceList();
    getContactorList();
    getWorks();
    //getWorkList(1);
    getWorksVis('',1);
    $("select[object-name='work_1part']").change(function(){getWorkList(2);});
    $("select[object-name='work_2part']").change(function(){getWorkList(3);});
    $("select[object-name='work_3part']").change(function(){getWorkList(4);});
});

function getWorkList(depth)
{
    var api = false;
    var item = "";
    //$('ul[object-name="work-'+depth+'"]').html('<li>Загрузка...</li>');
    if (depth == 1)
        parent = "all";
    else
        parent = $("select[object-name='work_"+(depth-1)+"part']").val();
    $.get('api.php?act=works&parent='+parent)
        .done(function(data) {api = data;})
        .fail(function() {return false;})
        .success(function(){
            api = $.parseJSON(api);
            if(api.code != '200')
            {
                alert("Не удалось загрузить список работ");
                return;
            }
            for(var i = 0; i < api.data.length; i++)
            {
                check = api.data[i].item_id.split(".");
                if (check.length != depth)
                continue;
                item += '<option class = "optWid" value = "'+api.data[i].item_id+'">'+api.data[i].text+'</option>';
            }
            $("select[object-name='work_"+depth+"part']").html(item);
            if(depth < 4)
                getWorkList(depth+1);
        });
}
function getPriceList()
{
    var api = '';
    $.get('api.php?act=catalog_price_list_get')
    .done(function(data) {api = data;})
    .fail(function() {return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {return false;}
        if(api.data.length < 1){$('div[object-name="price-list"]').html('<a href="#" class="list-group-item"><h4 class="list-group-item-heading">Расценок не обнаружено</h4><p class="list-group-item-text">Вы еще не создали ни одной расценки</p></a>'); return false;}
        var item = '';
        $('div[object-name="price-list"]').html('');
        for(var i=0;i<api.data.length;i++)
        {
            item = '<a href="#" object-id="'+api.data[i].id+'" class="list-group-item" onclick="priceGetData(this)"><h4 class="list-group-item-heading">'+api.data[i].name+'</h4>';
            item += '<p class="list-group-item-text" style="font-size: 10px;"><b>Заказчик:</b> '+api.data[i].customer.name+'<br /> <b>Подрядчик:</b> '+api.data[i].contactor.name+'</p></a>';
            $('div[object-name="price-list"]').append(item);
        }
    });
}
function priceGetData(e)
{
     $('div[object-name="price-list"] a').removeClass('active');
    $(e).addClass('active');
    var api = '';
    $.get('api.php?act=catalog_price_getdata&id='+$(e).attr('object-id'))
    .done(function(data) {api = data;})
    .fail(function() {return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {return false;}
        $('em[object-name="name"]').html(api.data.name);
        $('em[object-name="contact_number"]').html(api.data.contact_number);
        $('em[object-name="contact_date"]').html(api.data.contact_date);
        $('em[object-name="accord_number"]').html(api.data.accord_number);
        $('em[object-name="accord_date"]').html(api.data.accord_date);
        $('em[object-name="customer"]').html(api.data.customer.name);
        $('em[object-name="cnotactor"]').html(api.data.contactor.name);
        $('em[object-name="type"]').html((api.data.type=='ms') ? 'МС':((api.data.type=='drs') ? 'ДРС':'Unknown'));
        $('button[object-name="btnpupdate"]').attr('object-id',api.data.id);
        $('button[object-name="btnpdelete"]').attr('object-id',api.data.id);
        getItemsPrice(api.data.id);
    });
}
function saveUpdatePrice()
{
    var api = '';
    $.post('api.php?act=catalog_price_update',$('form[object-name="updatePriceForm"]').serialize())
    .done(function(data) {api = data;})
    .fail(function() {alert('Не удалось сохранить изменения');return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {
           alert('Не удалось сохранить изменения');
            return false;
        }
        getPriceList();
        //alert('Изменения сохранены');
        $('#update_price').modal('hide');
    });
}
function priceUpdate(e)
{
     var api = '';
    $(e).html('Загружаю...');
    $.get('api.php?act=catalog_price_getdata&id='+$(e).attr('object-id'))
    .done(function(data) {api = data;})
    .fail(function() {$(e).html('Редактирвать');return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {$(e).html('Редактирвать');return false;}
         $('form[object-name="updatePriceForm"] input[name="id"]').val(api.data.id);
        $('form[object-name="updatePriceForm"] input[name="name"]').val(api.data.name);
        $('form[object-name="updatePriceForm"] input[name="contact_number"]').val(api.data.contact_number);
        $('form[object-name="updatePriceForm"] input[name="contact_date"]').val(api.data.contact_date);
        $('form[object-name="updatePriceForm"] input[name="accord_number"]').val(api.data.accord_number);
        $('form[object-name="updatePriceForm"] input[name="accord_date"]').val(api.data.accord_date);
        $('form[object-name="updatePriceForm"] select[name="customer"]').val(api.data.customer.id);
        $('form[object-name="updatePriceForm"] select[name="cnotactor"]').val(api.data.contactor.id);
        $('form[object-name="updatePriceForm"] select[name="type"]').val(api.data.type);
        $('button[object-name="btnpupdate"]').attr('object-id',api.data.id);
        $('button[object-name="btnpdelete"]').attr('object-id',api.data.id);
        $('#update_price').modal('show');
        $(e).html('Редактирвать');
    });
}
function newPrice()
{
    $('#new_price').modal('show');
}
function saveNewPrice()
{
    var api = '';
    $.post('api.php?act=catalog_price_add',$('form[object-name="newPriceForm"]').serialize())
    .done(function(data) {api = data;})
    .fail(function() {alert('Не удалось создать расценку');return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {
            if(api.code == '404')
            {alert('Такое название уже используется, попропуйте сменить название');}
            else
            {alert('Не удалось создать расценку');}
            return false;
        }
        getPriceList();
        //alert('Новыая расценка создана');
         $('#new_price').modal('hide');
    });
}
function deletePrice(e)
{
    var api = '';
    $(e).html('Удаляю...');
    $.get('api.php?act=catalog_price_delete&id='+$(e).attr('object-id'))
    .done(function(data) {api = data;})
    .fail(function() {$(e).html('Редактирвать');return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {$(e).html('Удалить');return false;}
        getPriceList();
        //alert('Расценка удалена');
        $(e).html('Удалить');
    });
}
///
function newPriceItem()
{
    
    $('#new_price_item').modal('show');
    getWorks();
}
function getWorks()
{
    var api = false;
    //$('ul[object-name="work-'+depth+'"]').html('<li>Загрузка...</li>');
    $.get('api.php?act=works&parent=all')
    .done(function(data) {api = data;})
    .fail(function() {return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {
         //   $('ul[object-name="work-'+depth+'"]').html('<li>Ошибка...</li>');
            return false;
        }
        
       /* if(api.data.length < 1){$('ul[object-name="work-'+depth+'"]').html('<li>Нет данных</li>'); return false;}
        $('ul[object-name="work-'+depth+'"]').html('');
        */
       var item = '';
       $('tbody[object-name="price_content_list"]').html('');
       for(var i=0;i<api.data.length;i++)
        {
            check = api.data[i].item_id.split(".");
            item = '<tr list-item-id="'+api.data[i].id+'">';
            item += '<td>'+api.data[i].item_id+'</td>';
            item += '<td>'+api.data[i].text+'</td>';
            item += '<td>'+api.data[i].type+'</td>';
            item += '<td>'+api.data[i].min+'</td>';
            if(!(check.length == 1 || check.length == 2))
            {
                item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+api.data[i].retail+'" /></td>';
                item += '<td style="vertical-align: middle; text-aligN: center;"><a href="#" e-id="'+api.data[i].id+'" onclick="addNewItem(this)"><i class="glyphicon glyphicon-plus-sign"></i></a></td>';
             }
            else
            {
                item += "<td></td><td></td>";
            }
            item += '</tr>'
            $('tbody[object-name="price_content_list"]').append(item);
        }
        var co = 0;
        for(var i=1;i<=$('tbody[object-name="price_content"] tr').length;i++)
        {
            co = $('tbody[object-name="price_content"] tr:nth-child('+i+')').attr('price-el-id');
            $('tr[list-item-id="'+co+'"] td:nth-child(6) a i').removeClass('glyphicon-plus-sign').addClass('glyphicon glyphicon-minus-sign');
            $('tr[list-item-id="'+co+'"] td:nth-child(5) input').attr('disabled','');
            $('tr[list-item-id="'+co+'"] td:nth-child(5) input').val($('tbody[object-name="price_content"] tr:nth-child('+i+') td:nth-child(5)').html());
            $('tr[list-item-id="'+co+'"]').addClass('success');
        }
    });
}
function getItemsPrice(id)
{
    var api = '';
     $.get('api.php?act=price_item&do=get&id=0&parent='+id+'&retail=0')
    .done(function(data) {api = data;})
    .fail(function() {return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200'){return false;}
        var item = '';
        $('tbody[object-name="price_content"]').html('');
        for(var i=0;i<api.data.length;i++)
        {

            item = '<tr price-el-id="'+api.data[i].work.id+'">';
            item += '<td>'+(i+1)+'</td>';
            item += '<td>'+api.data[i].work.text+'</td>';
            item += '<td>'+api.data[i].work.type+'</td>';
            item += '<td>'+api.data[i].work.min+'</td>';
            item += '<td>'+api.data[i].retail+'</td>';
            item += '</tr>';
            $('tbody[object-name="price_content"]').append(item);
        }
    });
}
function addNewItem(e)
{
    var id = $(e).attr('e-id');
    var lid = $('div[object-name="price-list"] a.active').attr('object-id');
    var retail = $('tr[list-item-id="'+id+'"] td:nth-child(5) input').val().replace(',','.');
    var action = '';
    if($('tr[list-item-id="'+id+'"]').hasClass('success') == false)
    {
        $('tr[list-item-id="'+id+'"] td:nth-child(6) a i').removeClass('glyphicon-plus-sign').addClass('glyphicon glyphicon-minus-sign');
        $('tr[list-item-id="'+id+'"] td:nth-child(5) input').attr('disabled','');
        $('tr[list-item-id="'+id+'"]').addClass('success');
        action = 'append';
     }
    else
    {
        $('tr[list-item-id="'+id+'"] td:nth-child(6) a i').removeClass('glyphicon-minus-sign').addClass('glyphicon-plus-sign');
        $('tr[list-item-id="'+id+'"] td:nth-child(5) input').removeAttr('disabled');
        $('tr[list-item-id="'+id+'"]').removeClass('success');
        action = 'remove';
    }
    var api = '';
    $.get('api.php?act=price_item&do='+action+'&id='+id+'&parent='+lid+'&retail='+encodeURI(retail))
    .done(function(data) {api = data;})
    .fail(function() {return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200'){return false;}
        getItemsPrice(lid);
    });
    
}
function getWorksVis(parent,depth)
{
    parent_add = parent;
    var api = false;
    if(depth < 4)
    {
        for(var i=depth;i<5;i++)
        {
            $('ul[object-name="work-'+i+'"]').html('');
        }
    }
    $('ul[object-name="work-'+depth+'"]').html('<li>Загрузка...</li>');
//    alert($('select[object-name="contact-n"]').val());
        get = 'api.php?act=works&parent='+encodeURI(parent);
    $.get(get)
        .done(function(data) {api = data;})
        .fail(function() {$('ul[object-name="work-'+depth+'"]').html('<li>Ошибка...</li>');})
        .success(function(){
            api = $.parseJSON(api);
            if(api.code != '200')
            {
                $('ul[object-name="work-'+depth+'"]').html('<li>Ошибка...</li>');
                return false;
            }
            if(api.data.length < 1){$('ul[object-name="work-'+depth+'"]').html('<li>Нет данных</li>'); return false;}
            $('ul[object-name="work-'+depth+'"]').html('');
            for(var i=0;i<api.data.length;i++)
            {
                if($('table[object-name="price_content"] tbody tr[price-el-id="'+api.data[i].id+'"]').html() != null)
                {
                    api.data[i].retail = $('table[object-name="price_content"] tbody tr[price-el-id="'+api.data[i].id+'"] td:nth-child(1)').html();
                }
                $('ul[object-name="work-'+depth+'"]').append('<li><a href="#" data-toggle="tab" param-id="'+api.data[i].id+'" param-text="'+api.data[i].text+'" param-type="'+api.data[i].type+'" param-min="'+api.data[i].min+'" param-retail="'+api.data[i].retail+'"  param-item-id="'+api.data[i].item_id+'" oncontextmenu="addRowWork('+api.data[i].id+'); return false;" onclick="getWorksVis(\''+api.data[i].item_id+'\','+(depth+1)+')">'+api.data[i].text+'</a></li>');
            }
            next_num = api.data.length+1;
        });
}
function addWork()
{
    api = false;
    name = $("input[object-name='name']").val();
    min = $("input[object-name='min']").val();
    retail = $("input[object-name='retail']").val();
    console.log(name);
    console.log(min);
    console.log(retail);

    if ((name != null) && (min != null) && (retail != null))
    {
        type = min.split(" ");
        type = type[1];
        $.post("api.php?act=addWork",{
            name: name,
            min:min,
            type:type,
            retail:retail,
            parent:parent_add,
            next_num:next_num
        })
            .done(function(data){api = data;})
            .fail(function(){alert("Ошибка при обращении к серверу");})
            .success(function(){
                api = $.parseJSON(api);
                if (api.code=="200")
                {
                    alert("Добавлено");
                    getWorksVis(parent_add,(parent_add.split(".").length+1))
                }
                else
                {
                    if (api.code=="500")
                    alert("Работа с таким названием уже существует");
                    else
                    alert("Добавление не удалось");
                }
            })
    }
}
