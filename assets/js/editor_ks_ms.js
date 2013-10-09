$(document).ready(function() {
    getWorks('',1);
    if(typeof edit_id === 'undefined')
        getAtsList('ats');
    else
        getEditData();
    getContactorList();
    $('select[object-name="ats"]').change(function(){
        getClusterList('cluster',$(this).val());
        $('select[object-name="address"]').html('<option value="0">---</option>');
    });
    $('select[object-name="cluster"]').change(function(){
        getAddressByAtsCluster('address',$('select[object-name="ats"]').val(),$(this).val());
    });
    $('select[object-name="address"]').change(function(){
        if($('button[object-name="activatedAddress"]').is(':disabled') != false)
        {
            $('button[object-name="activatedAddress"]').removeAttr('disabled');
        }
    });
    $('button[object-action="addmaterial"]').click(function(){materialAddRow();});
    $('select[object-name="contractor"]').change(function(){getPriceList('ms');});
    $('select[object-name="customer"]').change(function(){getPriceList('ms');});
    $('select[object-name="contact-n"]').change(function(){
        getPriceData($(this).val());
    });
});
var currentAddressName = '';
var currentAddressId = '';
function selectAddress()
{
    if($('select[object-name="address"]').val() == '0')
    {
        alert('Данный адрес не может быть выбран.');
        return false;
    }
    var api = false;
    $('input[object-name="project-name"]').val('Загрузка...');
    $.get('api.php?act=address&get=address&address='+encodeURI($('select[object-name="address"]').val()))
    .done(function(data) {api = data;})
    .fail(function() {$('input[object-name="project-name"]').val('Ошибка...');})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {
            alert('Ошибка получения данных для адреса: '+$('select[object-name="address"]').val());
            return false;
        }
        if(api.data.length < 1){alert('Нет данных для адреса: '+$('select[object-name="address"]').val()); return false;}
        for(var i=2;i<=$('table[object-name="ks"] tbody').length;i++)
        {
            if($('table[object-name="ks"] tbody:nth-child('+i+')').attr('address-id') == api.data[0].id)
            {
                currentAddressName = $('select[object-name="address"]').val();
                currentAddressId = api.data[0].id;
                $('button[object-name="activatedAddress"]').attr('disabled','');
                return true;
            }
        }
        var item = '<tbody visible="show" address-id="'+api.data[0].id+'">';
        item    += '<tr>';
        item    += '<td style="vertical-align: middle; text-align: center;"><a href="#remove" onclick="addresstoggle(\''+api.data[0].id+'\')" title="Свернуть"><i class="glyphicon glyphicon-resize-small"></i></a></td>';
        item    += '<th colspan="6">'+$('select[object-name="address"]').val()+'</th>';
        item    += '<td style="vertical-align: middle;"><a href="#remove" onclick="workDelRows(\''+api.data[0].id+'\')" title="Удалить запись"><i class="glyphicon glyphicon-remove"></i></a></td>';
        item    += '</tr>';
        item    += '</tbody>';
        $('table[object-name="ks"]').append(item);
        currentAddressName = $('select[object-name="address"]').val();
        currentAddressId = api.data[0].id;
        $('button[object-name="activatedAddress"]').attr('disabled','');
        if (typeof edit_id !== 'undefined')
        {
            addRowWorkEditMs(i);
        }
        return true;
    });
}
function addresstoggle(id)
{
    if($('table[object-name="ks"] tbody[address-id="'+id+'"] tr').length < 2){return false;}
    var display = ($('table[object-name="ks"] tbody[address-id="'+id+'"]').attr('visible') == 'show') ? 'none':'table-row';
    for(var i=2;i<=$('table[object-name="ks"] tbody[address-id="'+id+'"] tr').length;i++)
    {
        $('table[object-name="ks"] tbody[address-id="'+id+'"] tr:nth-child('+i+')').css('display',display);
    }
    if(display == 'none'){
        $('table[object-name="ks"] tbody[address-id="'+id+'"]').attr('visible','hide');
        $('table[object-name="ks"] tbody[address-id="'+id+'"] tr:nth-child(1) td:nth-child(1) a').attr('title','Развернуть');
        $('table[object-name="ks"] tbody[address-id="'+id+'"] tr:nth-child(1) td:nth-child(1) a i').removeClass('glyphicon-resize-small').addClass('glyphicon-resize-full');
    }else{
        $('table[object-name="ks"] tbody[address-id="'+id+'"]').attr('visible','show');
        $('table[object-name="ks"] tbody[address-id="'+id+'"] tr:nth-child(1) td:nth-child(1) a').attr('title','Свернуть');
        $('table[object-name="ks"] tbody[address-id="'+id+'"] tr:nth-child(1) td:nth-child(1) a i').removeClass('glyphicon-resize-full').addClass('glyphicon-resize-small');}
}
function addRowWork(id)
{
    if(currentAddressName == '' && currentAddressId == '')
    {
        alert('Не выбран адрес');
        return false;
    }
    if($('button[object-name="activatedAddress"]').is(':disabled') != true)
    {
        alert('Адрес был изменени, для добавления новой записи необходимо активировать установленный адрес.');
        return false;
    }
          id = $('a[param-id="'+id+'"]').attr('param-id');
    var text = $('a[param-id="'+id+'"]').attr('param-text');
    var type = $('a[param-id="'+id+'"]').attr('param-type');
    var item_id = $('a[param-id="'+id+'"]').attr('param-item-id');
    var retail = $('a[param-id="'+id+'"]').attr('param-retail');
    var min = $('a[param-id="'+id+'"]').attr('param-min');
    var eid = 0;
    if(($('table[object-name="ks"] tbody[address-id="'+currentAddressId+'"] tr').length) > 1)
    {
        eid = $('table[object-name="ks"] tbody[address-id="'+currentAddressId+'"] tr').length;
        eid = parseInt($('table[object-name="ks"] tbody[address-id="'+currentAddressId+'"] tr:nth-child('+eid+') td:nth-child(1)').html());
        eid += 1;
    }
    else
    {
        eid = 1;
    }
    var now = new Date().getTime() / 1000;
    var item = '<tr tr-item-id="work-'+now+'" item-wid="'+id+'">';
    item += '<td style="text-align: center; vertical-align: middle; font-weight: bold;">'+eid+'</td>';
    item += '<td>'+text+'</td>';
    item += '<td style="text-align: center; vertical-align: middle; font-weight: bold;">'+item_id+'</td>';
    item += '<td style="text-align: center; vertical-align: middle; font-weight: bold;">'+min+'</td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="0" onkeyup="workCalcRow(\''+now+'\')"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+retail+'" onkeyup="workCalcRow(\''+now+'\')"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="0"/></td>';
    item += '<td style="vertical-align: middle;"><a href="#remove" onclick="workDelRow(\''+now+'\')" title="Удалить запись"><i class="glyphicon glyphicon glyphicon glyphicon-remove"></i></a></td>';
    item +='</tr>';
    $('table[object-name="ks"] tbody[address-id="'+currentAddressId+'"]').append(item);
}

function workCalcRow(id)
{
    var lim = parseFloat($('tr[tr-item-id="work-'+(id)+'"]  td:nth-child(4)').html().replace(/[^0-9|.|,]/g, "").replace(',','.'));
    var value = parseFloat($('tr[tr-item-id="work-'+(id)+'"] td:nth-child(5) input').val().replace(',','.'));
     var price = parseFloat($('tr[tr-item-id="work-'+(id)+'"] td:nth-child(6) input').val().replace(',','.'));
     var full = value * price;
     $('tr[tr-item-id="work-'+(id)+'"] td:nth-child(7) input').val(full.toFixed(2));
}
function workRecalc()
{
    /*if($('tbody[object-name="ks"] tr').length < 1) return false;
    for(var i=1;i<=$('tbody[object-name="ks"] tr').length;i++)
    {
        $('tbody[object-name="ks"] tr:nth-child('+i+')').attr('tr-item-id','work-'+i);
        $('tbody[object-name="ks"] tr:nth-child('+i+') td:nth-child(1)').html(i);
        $('tbody[object-name="ks"] tr:nth-child('+i+') td:nth-child(5) input').attr('onkeyup','workCalcRow(\''+i+'\')');
        $('tbody[object-name="ks"] tr:nth-child('+i+') td:nth-child(6) input').attr('onkeyup','workCalcRow(\''+i+'\')');
    }*/
}
function workDelRow(id)
{
    $('tr[tr-item-id="work-'+(id)+'"]').remove();
}
function workDelRows(id)
{
    $('tbody[address-id="'+id+'"]').remove();
}
//////////////////////

function materialCalcRow(id)
{
     var value = parseFloat($('tbody[object-name="material"] tr[tr-item-id="material-'+(id)+'"] td:nth-child(6) input').val().replace(',','.'));
     var price = parseFloat($('tbody[object-name="material"] tr[tr-item-id="material-'+(id)+'"] td:nth-child(7) input').val().replace(',','.'));
     var full = value * price;
    $('tbody[object-name="material"] tr[tr-item-id="material-'+(id)+'"] td:nth-child(8) input').val(full.toFixed(2));
}
function materialDelRow(id)
{
    $('tbody[object-name="material"] tr[tr-item-id="material-'+(id)+'"]').remove();
}
function materialAddRow()
{
    var id = 0;
    if($('tbody[object-name="material"] tr').length > 0)
    {
        id = $('tbody[object-name="material"] tr').length;
        id = parseInt($('tbody[object-name="material"] tr:nth-child('+id+') td:nth-child(1)').html());
        id += 1;
    }
    else
    {
        id = 1;
    }
    var item = '<tr tr-item-id="material-'+(id)+'">';
    item += '<td style="text-align: center; font-weight: bold;">'+id+'</td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;"/></td>';
    item += '<td><input type="text" class="form-control"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" onkeyup="materialCalcRow(\''+id+'\')" value="0"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" onkeyup="materialCalcRow(\''+id+'\')" value="0"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="0"/></td>';
    item += '<td style="vertical-align: middle;"><a href="#remove" onclick="materialDelRow(\''+id+'\')" title="Удалить запись"><i class="glyphicon glyphicon glyphicon glyphicon-remove"></i></a></td>';
    item += '</tr>';
    $('tbody[object-name="material"]').append(item);
}
function mathCompens()
{
    var x = 0;
    var isset = false;
    var v = 0;
    var item = '';
    //var str = "df49fkwва,п39п0sD   F@rtsbG.H76fgo3ывоа";
    //alert(str.replace(/[^0-9|.|,]/g, ""));

    $('tbody[object-name="compens"]').html('');
    for(var i=2;i<=$('table[object-name="ks"] tbody').length;i++)
    {
        for(var j=2;j<=$('table[object-name="ks"] tbody:nth-child('+i+') tr').length;j++)
        {
            isset = false;
            x = parseFloat($('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(5) input').val().replace(',','.'));
            x %=parseFloat($('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(4)').html().replace(/[^0-9|.|,]/g, "").replace(',','.'));
            if(x >0)
            {
                for(var k=1;k<=$('tbody[object-name="compens"] tr').length;k++)
                {
                    
                    if($('tbody[object-name="compens"] tr:nth-child('+k+')').attr('item-id') == $('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+')').attr('item-wid'))
                    {
                        v = parseFloat($('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(5) input').val().replace(',','.'));
                        v %=parseFloat($('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(4)').html().replace(/[^0-9|.|,]/g, "").replace(',','.'));
                        v = parseFloat($('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(4)').html().replace(/[^0-9|.|,]/g, "").replace(',','.')) - v;
                        v = parseFloat(v.toFixed(2))+parseFloat($('tbody[object-name="compens"] tr:nth-child('+k+') td:nth-child(4) input').val().replace(',','.'));
                        $('tbody[object-name="compens"] tr:nth-child('+k+') td:nth-child(4) input').val(v);
                        v = parseFloat($('tbody[object-name="compens"] tr:nth-child('+k+') td:nth-child(4) input').val().replace(',','.'));
                        v *= parseFloat($('tbody[object-name="compens"] tr:nth-child('+k+') td:nth-child(5) input').val().replace(',','.'));
                        v = v.toFixed(2);
                        $('tbody[object-name="compens"] tr:nth-child('+k+') td:nth-child(6) input').val(v);
                        isset = true;
                        break;
                    }
                }
                if(!isset)
                {
                    item = '<tr item-id="'+$('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+')').attr('item-wid')+'">';
                    item += '<td>'+($('tbody[object-name="compens"] tr').length+1)+'</td>';
                    item += '<td>'+$('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(2)').html()+'</td>';
                    item += '<td>'+$('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(4)').html()+'</td>';
                    v = ($('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(5) input').val().replace(',','.'));
                    v %=parseFloat($('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(4)').html().replace(/[^0-9|.|,]/g, "").replace(',','.'));
                    v = parseFloat($('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(4)').html().replace(/[^0-9|.|,]/g, "").replace(',','.')) - v;
//v = v.replace(",", ".").split('.');
                    //v = (1-parseFloat('0.'+v[1]));
                    item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+v.toFixed(2)+'" /></td>';
                    item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+$('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(6) input').val()+'" /></td>';
                    v *= parseFloat(($('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(6) input').val().replace(',','.')));
                    v = v.toFixed(2);
                    item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+v+'" /></td>';
                    item += '</tr>';
                    $('tbody[object-name="compens"]').append(item);
                }
            }
        }
    }
}
function save_ms()
{
    var prepayment = parseFloat($('input[object-name="prepayment"]').val().replace(',','.')).toFixed(2);
    var date_a = $('input[object-name="date-start"]').val();
    var date_b = $('input[object-name="date-stop"]').val();
    var price  = $('select[object-name="contact-n"]').val();
    var doc_n  = $('input[object-name="document-n"]').val();
    var work_name = $('input[object-name="work-name"]').val();
    var cluster =  $('select[object-name="cluster"]').val();
    var ats =  $('select[object-name="ats"]').val();
    var works = '';
    var material = '';
    var compens = '';
    console.log(prepayment);
    for(var i=2;i<=$('table[object-name="ks"] tbody').length;i++)
    {
        for(var j=2;j<=$('table[object-name="ks"] tbody:nth-child('+i+') tr').length;j++)
        {
            works += $('table[object-name="ks"] tbody:nth-child('+i+')').attr('address-id')+';';
            works += $('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+')').attr('item-wid')+';';
            works += $('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(5) input').val()+';'
            works += $('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(6) input').val()+';'
            works += $('table[object-name="ks"] tbody:nth-child('+i+') tr:nth-child('+j+') td:nth-child(7) input').val();
            works += "\r\n";
        }
    }
    for(var i=1;i<=$('tbody[object-name="compens"] tr').length;i++)
    {
        compens += $('tbody[object-name="compens"] tr:nth-child('+i+')').attr('item-id')+';';
        compens += $('tbody[object-name="compens"] tr:nth-child('+i+') td:nth-child(4) input').val()+';';
        compens += $('tbody[object-name="compens"] tr:nth-child('+i+') td:nth-child(5) input').val()+';';
        compens += $('tbody[object-name="compens"] tr:nth-child('+i+') td:nth-child(6) input').val();
        compens += "\r\n";
    }
    for(var i=1;i<=$('tbody[object-name="material"] tr').length;i++)
    {
        material += $('tbody[object-name="material"] tr:nth-child('+i+') td:nth-child(2) input').val().replace(';','::')+';';
        material += $('tbody[object-name="material"] tr:nth-child('+i+') td:nth-child(3) input').val().replace(';','::')+';';
        material += $('tbody[object-name="material"] tr:nth-child('+i+') td:nth-child(4) input').val().replace(';','::')+';';
        material += $('tbody[object-name="material"] tr:nth-child('+i+') td:nth-child(5) input').val().replace(';','::')+';';
        material += $('tbody[object-name="material"] tr:nth-child('+i+') td:nth-child(6) input').val().replace(';','::')+';';
        material += $('tbody[object-name="material"] tr:nth-child('+i+') td:nth-child(7) input').val().replace(';','::')+';';
        material += $('tbody[object-name="material"] tr:nth-child('+i+') td:nth-child(8) input').val().replace(';','::');
        material += "\r\n";
    }
    var api ='';
    $.post('api.php?act=save_ks&type=ms',{ats:ats,cluster:cluster,work_name:work_name,doc_n:doc_n,price:price,date_a:date_a,date_b:date_b,works:works,material:material,compens:compens,prepayment:prepayment})
    .done(function(data) {api = data;})
    .fail(function() {$('input[object-name="project-name"]').val('<option value="0">Ошибка...</option>');})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {alert('Ошибка сохранения...'); return false;}
        alert('Данные сохранены... Для просмотра воспользуйтесь каталогом Каталоги->Формы КС');
    });
}

function getEditData()
{
    try
    {
        api = false;
        $.get('api.php?act=ks_data&id='+edit_id)
            .done(function(data){api = data;})
            .fail(function(){
                try_count++;
                if (try_count<3)
                    getEditData();
                else
                    return false;
            })
            .success(function(){ api = $.parseJSON(api); edit_data = api.data; getAtsList('ats')})
    }
    catch(exc)
    {

    }
}

function addRowWorkEditMs(i)
{
    if(currentAddressName == '' && currentAddressId == '')
    {
        console.log("не выбран адрес1");
        alert('Не выбран адрес');
        return false;
    }
    if($('button[object-name="activatedAddress"]').is(':disabled') != true)
    {
        console.log("не выбран адрес2");
        alert('Адрес был изменени, для добавления новой записи необходимо активировать установленный адрес.');
        return false;
    }
    id = edit_data['content'][i]['id'];
    var text = edit_data['content'][i]['text'];
    var type = edit_data['content'][i]['type'];
    var item_id = edit_data['content'][i]['item_id'];
    var retail = edit_data['content'][i]['retail'];
    var min = edit_data['content'][i]['min'];
    var eid = 0;
    if(($('table[object-name="ks"] tbody[address-id="'+currentAddressId+'"] tr').length) > 1)
    {
        eid = $('table[object-name="ks"] tbody[address-id="'+currentAddressId+'"] tr').length;
        eid = parseInt($('table[object-name="ks"] tbody[address-id="'+currentAddressId+'"] tr:nth-child('+eid+') td:nth-child(1)').html());
        eid += 1;
    }
    else
    {
        eid = 1;
    }
    var now = new Date().getTime() / 1000;
    var item = '<tr tr-item-id="work-'+now+'" item-wid="'+id+'">';
    item += '<td style="text-align: center; vertical-align: middle; font-weight: bold;">'+eid+'</td>';
    item += '<td>'+text+'</td>';
    item += '<td style="text-align: center; vertical-align: middle; font-weight: bold;">'+item_id+'</td>';
    item += '<td style="text-align: center; vertical-align: middle; font-weight: bold;">'+min+'</td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+edit_data['content'][i]['value']+'" onkeyup="workCalcRow(\''+now+'\')"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+retail+'" onkeyup="workCalcRow(\''+now+'\')"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="0"/></td>';
    item += '<td style="vertical-align: middle;"><a href="#remove" onclick="workDelRow(\''+now+'\')" title="Удалить запись"><i class="glyphicon glyphicon glyphicon glyphicon-remove"></i></a></td>';
    item +='</tr>';
    $('table[object-name="ks"] tbody[address-id="'+currentAddressId+'"]').append(item);
    workCalcRow(now);
    console.log("Работа "+i+" добавлена");
    if(i<edit_data['content'].length-1)
        i++;
    else
        return;
    if ((i == 0) || (edit_data['content'][i]['address']['address'] != edit_data['content'][i-1]['address']['address']) )
    {
        $('select[object-name="address"] option[value="'+edit_data['content'][i]['address']['address']+'"]').attr("selected","selected");
        selectAddressEdit(i);
    }
    else
    {
        console.log("тот же адрес");
        addRowWorkEditMs(i);
    }
    //mathCompens();
}


function selectAddressEdit(j)
{
    if($('select[object-name="address"]').val() == '0')
    {
        alert('Данный адрес не может быть выбран.');
        return false;
    }
    var api = false;
    $('input[object-name="project-name"]').val('Загрузка...');
    $.get('api.php?act=address&get=address&address='+encodeURI($('select[object-name="address"]').val()))
        .done(function(data) {api = data;})
        .fail(function() {$('input[object-name="project-name"]').val('Ошибка...');})
        .success(function(){
            api = $.parseJSON(api);
            if(api.code != '200')
            {
                alert('Ошибка получения данных для адреса: '+$('select[object-name="address"]').val());
                return false;
            }
            if(api.data.length < 1){alert('Нет данных для адреса: '+$('select[object-name="address"]').val()); return false;}
            for(var i=2;i<=$('table[object-name="ks"] tbody').length;i++)
            {
                if($('table[object-name="ks"] tbody:nth-child('+i+')').attr('address-id') == api.data[0].id)
                {
                    currentAddressName = $('select[object-name="address"]').val();
                    currentAddressId = api.data[0].id;
                    $('button[object-name="activatedAddress"]').attr('disabled','');
                    return true;
                }
            }
            var item = '<tbody visible="show" address-id="'+api.data[0].id+'">';
            item    += '<tr>';
            item    += '<td style="vertical-align: middle; text-align: center;"><a href="#remove" onclick="addresstoggle(\''+api.data[0].id+'\')" title="Свернуть"><i class="glyphicon glyphicon-resize-small"></i></a></td>';
            item    += '<th colspan="6">'+$('select[object-name="address"]').val()+'</th>';
            item    += '<td style="vertical-align: middle;"><a href="#remove" onclick="workDelRows(\''+api.data[0].id+'\')" title="Удалить запись"><i class="glyphicon glyphicon-remove"></i></a></td>';
            item    += '</tr>';
            item    += '</tbody>';
            $('table[object-name="ks"]').append(item);
            currentAddressName = $('select[object-name="address"]').val();
            currentAddressId = api.data[0].id;
            $('button[object-name="activatedAddress"]').attr('disabled','');
            if (typeof edit_id !== 'undefined')
            {
                addRowWorkEditMs(j);
            }
            return true;
        });
}

function materialAddRowEdit(i)
{
    var id = 0;
    if($('tbody[object-name="material"] tr').length > 0)
    {
        id = $('tbody[object-name="material"] tr').length;
        id = parseInt($('tbody[object-name="material"] tr:nth-child('+id+') td:nth-child(1)').html());
        id += 1;
    }
    else
    {
        id = 1;
    }
    var item = '<tr tr-item-id="material-'+(id)+'">';
    item += '<td style="text-align: center; font-weight: bold;">'+id+'</td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value = "'+edit_data['material'][i]['number']+'"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+edit_data['material'][i]['party']+'"/></td>';
    item += '<td><input type="text" class="form-control" value="'+edit_data['material'][i]['name']+'"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+edit_data['material'][i]['type']+'"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" onkeyup="materialCalcRow(\''+id+'\')" value="'+edit_data['material'][i]['value']+'"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" onkeyup="materialCalcRow(\''+id+'\')" value="'+edit_data['material'][i]['retail']+'"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="0"/></td>';
    item += '<td style="vertical-align: middle;"><a href="#remove" onclick="materialDelRow(\''+id+'\')" title="Удалить запись"><i class="glyphicon glyphicon glyphicon glyphicon-remove"></i></a></td>';
    item += '</tr>';
    $('tbody[object-name="material"]').append(item);
    materialCalcRow(id);
}

function compensRowAdd(i)
{
    item = '<tr item-id="'+edit_data['compens'][i]['id']+'">';
    item += '<td>'+($('tbody[object-name="compens"] tr').length+1)+'</td>';
    item += '<td>'+edit_data['compens'][i]['text']+'</td>';
    item += '<td style="text-align: center;">'+edit_data['compens'][i]['min']+'</td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+edit_data['compens'][i]['value']+'" /></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+edit_data['compens'][i]['price']+'" /></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="'+edit_data['compens'][i]['retail']+'" /></td>';
    item += '</tr>';
    $('tbody[object-name="compens"]').append(item);
}