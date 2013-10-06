$(document).ready(function() {
getAtsList('ats');
$('select[object-name="ats"]').change(function(){getClusterList('cluster',$(this).val());});
});
function getAtsList(object)
{
    var api = false;
    $('select[object-name="'+object+'"]').html('<option value="0">Загрузка...</option>');
    $.get('api.php?act=address&get=ats')
    .done(function(data) {api = data;})
    .fail(function() {$('select[object-name="'+object+'"]').html('<option value="0">Ошибка...</option>');})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {
            $('select[object-name="'+object+'"]').html('<option value="0">Ошибка...</option>');
            return false;
        }
        if(api.data.length < 1){$('select[object-name="'+object+'"]').html('<option value="0">Нет данных</option>'); return false;}
        $('select[object-name="'+object+'"]').html('');
        for(var i=0;i<api.data.length;i++)
        {
            $('select[object-name="'+object+'"]').append('<option value="'+api.data[i]+'">'+api.data[i]+'</option>');
        }
        getClusterList('cluster',$('select[object-name="'+object+'"]').val());
    });
}
function getClusterList(object,ats)
{
    var api = false;
    $('select[object-name="'+object+'"]').html('<option value="0">Загрузка...</option>');
    $.get('api.php?act=address&get=cluster-by-ats&ats='+encodeURI(ats))
    .done(function(data) {api = data;})
    .fail(function() {$('select[object-name="'+object+'"]').html('<option value="0">Ошибка...</option>');})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {
            $('select[object-name="'+object+'"]').html('<option value="0">Ошибка...</option>');
            return false;
        }
        if(api.data.length < 1){$('select[object-name="'+object+'"]').html('<option value="0">Нет данных</option>'); return false;}
        $('select[object-name="'+object+'"]').html('');
        for(var i=0;i<api.data.length;i++)
        {
            $('select[object-name="'+object+'"]').append('<option value="'+api.data[i].cluster+'">'+api.data[i].cluster+'</option>');
        }
        getAddressByAtsCluster(ats,$('select[object-name="'+object+'"]').val());
    });
}
function getAddressByAtsCluster(ats,cluster)
{
    var api = false;
    $('select[object-name="address"]').html('<option value="0">Загрузка...</option>');
    $.get('api.php?act=address&get=address-by-ats-cluster&ats='+encodeURI(ats)+'&cluster='+encodeURI(cluster))
    .done(function(data) {api = data;})
    .fail(function() {$('select[object-name="address"]').html('<option value="0">Ошибка...</option>');})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {
            $('select[object-name="address"]').html('<option value="0">Ошибка...</option>');
            return false;
        }
        if(api.data.length < 1){$('ul[object-name="address"]').html('<option value="0">Нет данных</option>'); return false;}
        $('select[object-name="address"]').html('');
        for(var i=0;i<api.data.length;i++)
        {
            $('select[object-name="address"]').append('<option value="'+api.data[i].address+'">'+api.data[i].address+'</option>');
        }
    });
}
function getKsDrsList(type)
{
    var api = '';
    $.get('api.php?act=ks_list&type='+type+'&ats='+$('select[object-name="ats"]').val()+'&cluster='+$('select[object-name="cluster"]').val())
    .done(function(data) {api = data;})
    .fail(function() {return false;})
    .success(function(){
        var item = '';
        api = $.parseJSON(api);
        if(api.code != '200'){alert('Ошибка...');return false;}
        $('div[object-name="ks-list"]').html('');
        for(var i=0;i<api.data.length;i++)
        {
            item = '<a href="#" class="list-group-item"';
            item += 'onclick="getKsData(this,\''+type+'\')" ';
            item += 'ks-id="'+api.data[i].id+'">';
            item += '<h4 class="list-group-item-heading">'+api.data[i].cable+'</h4>';
            item += '<p class="list-group-item-text" style="font-size: 10px;">';
            item += '<b>Проект:</b> '+api.data[i].cable+'<br />';
            item += '<b>№ Документа:</b> '+api.data[i].number+'<br />';
            date_b = api.data[i].date_stop;
            date_b = date_b.split("-");
            item += '<b>От:</b> '+date_b[2]+"-"+date_b[1]+"-"+date_b[0]+'</p></a>';
            $('div[object-name="ks-list"]').append(item);
        }
    });
}
function getKsData(e,type)
{
    $('div[object-name="ks_download"]').html('');
    var api = '';
    var id = $(e).attr('ks-id');
    $('div[object-name="ks-list"] a').removeClass('active');
    $(e).addClass('active');
    $.get('api.php?act=ks_data&id='+id)
    .done(function(data) {api = data;})
    .fail(function() {return false;})
    .success(function(){
        api = $.parseJSON(api);
//        console.log(api);
        err = null;
        try
        {
            $('h3[object-name="project"]').html(api.data.cable);
        }
        catch(e)
        {
            $('h3[object-name="project"]').html("Не задано");
            err = "a";
        }
        try
        {
            $('em[object-name="customer"]').html(api.data.contract.customer.name);
        }
        catch(e)
        {
            $('em[object-name="customer"]').html("Не задано");
            err = "a";
        }
        try
        {
            $('em[object-name="contractor"]').html(api.data.contract.contactor.name);
        }
        catch(e)
        {
            $('em[object-name="contractor"]').html("Не задано");
            err = "a";
        }
        try
        {
            $('em[object-name="doc_n"]').html(api.data.number);
        }
        catch(e)
        {
            $('em[object-name="doc_n"]').html("Не задано");
            err = "a";
        }
        try
        {
            date_a = api.data.date_start;
            date_a = date_a.split("-");
            $('em[object-name="date_a"]').html(date_a[2]+"-"+date_a[1]+"-"+date_a[0]);
        }
        catch(e)
        {
            $('em[object-name="date_a"]').html("Не задано");
            err = "a";
        }
        try
        {
            date_b = api.data.date_stop;
            date_b = date_b.split("-");
            $('em[object-name="date_b"]').html(date_b[2]+"-"+date_b[1]+"-"+date_b[0]);
        }
        catch(e)
        {
            $('em[object-name="date_b"]').html("Не задано");
            err = "a";
        }
        try
        {
            $('em[object-name="wname"]').html(api.data.name);
        }
        catch(e)
        {
            $('em[object-name="wname"]').html("Не задано");
            err = "a";
        }
        try
        {
            $('em[object-name="contract"]').html(api.data.contract.contact_number);
        }
        catch(e)
        {
            $('em[object-name="contract"]').html("Не задано");
            err = "a";
        }
        buildKsData(api.data,type,err);
    });
}
function buildKsData(data,type,err)
{
    var item ='';
    var r = 0;
    if(data.content.length > 0)
    {
         item = '<h3>Работы</h3><table class="table table-bordered"><thead><tr>';
         item += '<th rowspan="2" style="text-align: center; vertical-align: middle; width: 50px;">№№</th>';
         item += '<th rowspan="2" style="text-align: center; vertical-align: middle;">Наименование работ</th>';
         item += '<th rowspan="2" style="text-align: center; vertical-align: middle; width: 30px;">Номер Единичной расценки</th>';
         item += '<th rowspan="2" style="text-align: center; vertical-align: middle; width: 30px;">Еденица измерения</th>';
         item += '<th colspan="3" style="text-align: center;">Выполнено работ</th>';
         item += '</tr><tr>';
         item += '<th  style="text-align: center; vertical-align: middle; width: 30px;">Количество</th>';
         item += '<th  style="text-align: center; vertical-align: middle; width: 100px;">Цена за ед., руб.</th>';
         item += '<th  style="text-align: center; vertical-align: middle; width: 30px;">Стоимость, руб.</th>';
         item += '</tr></thead><tbody></tbody></table>';
         $('div[object-name="works"]').html(item);
        for(var i=0;i<data.content.length;i++)
        {
            item = '<tr>';
            item += '<td style="text-align: center; vertical-align: middle; font-weight: bold;">'+(i+1)+'</td>';
            item += '<td>'+data.content[i].text+'</td>';
            item += '<td style="text-align: center; vertical-align: middle; font-weight: bold;">'+data.content[i].item_id+'</td>';
            item += '<td style="text-align: center; vertical-align: middle; font-weight: bold;">'+data.content[i].min+'</td>';
            item += '<td>'+data.content[i].value+'</td>';
            item += '<td>'+data.content[i].retail+'</td>';
            r = (parseFloat(data.content[i].value.replace(',','.'))/parseFloat(data.content[i].min.replace(',','.')))*parseFloat(data.content[i].retail.replace(',','.'));
            item += '<td>'+r.toFixed(2)+'</td>';
            item +='</tr>';
            $('div[object-name="works"] table tbody').append(item);
        }
    }
    if(data.material.length > 0)
    {
        item = '<h3>Материалы</h3><table class="table table-bordered">';
        item += '<thead>';
        item += '<th style="text-align: center; vertical-align: middle; width: 50px;">№№</th>';
        item += '<th style="text-align: center; vertical-align: middle; width: 100px;">Номер материала</th>';
        item += '<th style="text-align: center; vertical-align: middle; width: 130px;">Партия</th>';
        item += '<th style="text-align: center; vertical-align: middle;">Наименование материала</th>';
        item += '<th style="text-align: center; vertical-align: middle; width: 30px;">Ед. измерения</th>';
        item += '<th style="text-align: center; vertical-align: middle; width: 50px;">Количество</th>';
        item += '<th style="text-align: center; vertical-align: middle; width: 50px;">Цена</th>';
        item += '<th style="text-align: center; vertical-align: middle; width: 50px;">Сумма</th>';
        item += '</thead>';
        item += '<tbody>';
        item += '</tbody>';
        item += '</table>';
        $('div[object-name="material"]').html(item);
        for(var i=0;i<data.material.length;i++)
        {
            item = '<tr>';
            item += '<td style="text-align: center; font-weight: bold;">'+(i+1)+'</td>';
            item += '<td>'+data.material[i].number+'</td>';
            item += '<td>'+data.material[i].party+'</td>';
            item += '<td>'+data.material[i].name+'</td>';
            item += '<td>'+data.material[i].type+'</td>';
            item += '<td>'+data.material[i].value+'</td>';
            item += '<td>'+data.material[i].price+'</td>';
            item += '<td>'+data.material[i].retail+'</td>';
            item += '</tr>';
            $('div[object-name="material"] table tbody').append(item);
        }
    }  
    if(data.compens.length > 0)
    {
        item = '<h3>Компенсации</h3><table class="table table-bordered"><thead><tr>';
        item += '<th style="text-align: center; vertical-align: middle; width: 50px;">№</th>';
        item += '<th style="text-align: center; vertical-align: middle;">Наименование</th>';
        item += '<th style="text-align: center; vertical-align: middle; width: 30px;">Ед. измерения</th>';
        item += '<th style="text-align: center; vertical-align: middle; width: 100px;">Объем</th>';
        item += '<th style="text-align: center; vertical-align: middle; width: 100px;">Цена за ед., руб.</th>';
        item += '<th style="text-align: center; vertical-align: middle; width: 100px;">Стоимость, руб.</th>';
        item += '</tr></thead><tbody></tbody></table>';
        $('div[object-name="compens"]').html(item);
        for(var i=0;i<data.compens.length;i++)
        {
            item = '<tr>';
            item += '<td style="text-align: center; font-weight: bold;">'+(i+1)+'</td>';
            item += '<td>'+data.compens[i].text+'</td>';
            item += '<td>'+data.compens[i].type+'</td>';
            item += '<td>'+data.compens[i].value+'</td>';
            item += '<td>'+data.compens[i].price+'</td>';
            item += '<td>'+data.compens[i].retail+'</td>';
            item += '</tr>';
            $('div[object-name="compens"] table tbody').append(item);
        }
    }


    $('div[object-name="ks_download"]').html('<p><a class="btn btn-primary" id="ks2" href="print.php?act=print&id='+data.id+'&type=2" target="_blank"><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp;Загрузить КС 2</a></p>');
    $('div[object-name="ks_download"]').append('<p><a class="btn btn-primary" id="ks3" href="print.php?act=print&id='+data.id+'&type=3" target="_blank"><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp;Загрузить КС 3</a></p>');
    $('div[object-name="ks_download"]').append('<p><a class="btn btn-primary" id="ks11" href="print.php?act=print&id='+data.id+'&type=11" target="_blank"><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp;Загрузить КС 11</a></p>');

    if (err != null)
    {
        $("#ks2").attr("disabled", "disabled");
        $("#ks3").attr("disabled", "disabled");
        $("#ks11").attr("disabled", "disabled");
    }
    if (type == 'drs')
        $('div[object-name="ks_download"]').append('<p><a class="btn btn-primary" href="?act=editor_ks_drs&cable='+data.id+'"><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp;Редактировать ДРС</a></p>');
    if (type == 'ms')
        $('div[object-name="ks_download"]').append('<p><a class="btn btn-primary" href="?act=editor_ks_ms&cable='+data.id+'"><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp;Редактировать МС</a></p>');
}