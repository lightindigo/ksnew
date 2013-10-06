var try_count = 0;
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
        getProjectName($(this).val());
    });
    $('button[object-action="addmaterial"]').click(function(){materialAddRow();});
    $('select[object-name="contractor"]').change(function(){getPriceList('drs');});
    $('select[object-name="customer"]').change(function(){getPriceList('drs');});
    $('select[object-name="contact-n"]').change(function(){
        getPriceData($(this).val());
        getItemsPrice($(this).val());
    });
    //getPriceData
});
function materialRecalc()
{
    if($('tbody[object-name="material"] tr').length < 1) return false;
    for(var i=1;i<=$('tbody[object-name="material"] tr').length;i++)
    {
        $('tbody[object-name="material"] tr:nth-child('+i+')').attr('tr-item-id','material-'+i);
        $('tbody[object-name="material"] tr:nth-child('+i+') td:nth-child(1)').html(i);
        $('tbody[object-name="material"] tr:nth-child('+i+') td:nth-child(6) input').attr('onkeyup','materialCalcRow(\''+i+'\')');
        $('tbody[object-name="material"] tr:nth-child('+i+') td:nth-child(7) input').attr('onkeyup','materialCalcRow(\''+i+'\')');
        $('tbody[object-name="material"] tr:nth-child('+i+') td:nth-child(9) a').attr('onclick','materialDelRow(\''+i+'\')');
    }
}
function workRecalc()
{
    if($('tbody[object-name="ks"] tr').length < 1) return false;
    for(var i=1;i<=$('tbody[object-name="ks"] tr').length;i++)
    {
        $('tbody[object-name="ks"] tr:nth-child('+i+') td:nth-child(1)').html(i);
    }
}
function materialCalcRow(id)
{
     var value = parseFloat($('tbody[object-name="material"] tr[tr-item-id="material-'+(id)+'"] td:nth-child(6) input').val().replace(',','.'));
     var price = parseFloat($('tbody[object-name="material"] tr[tr-item-id="material-'+(id)+'"] td:nth-child(7) input').val().replace(',','.'));
     var full = value * price;
     $('tbody[object-name="material"] tr[tr-item-id="material-'+(id)+'"] td:nth-child(8) input').val(full.toFixed(2));
}
function workCalcRow(id)
{
    var lim = parseFloat($('tbody[object-name="ks"] tr[tr-item-id="work-'+(id)+'"]  td:nth-child(4)').html().replace(/[^0-9|.|,]/g, "").replace(',','.'));
    var value = parseFloat($('tbody[object-name="ks"] tr[tr-item-id="work-'+(id)+'"] td:nth-child(5) input').val().replace(',','.'));
     var price = parseFloat($('tbody[object-name="ks"] tr[tr-item-id="work-'+(id)+'"] td:nth-child(6) input').val().replace(',','.'));
     var full = (value/lim) * price;
     $('tbody[object-name="ks"] tr[tr-item-id="work-'+(id)+'"] td:nth-child(7) input').val(full.toFixed(2));
}
function workDelRow(id)
{
    $('tbody[object-name="ks"] tr[tr-item-id="work-'+(id)+'"]').remove();
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
function getProjectName(address)
{
    var api = false;
    $('select[object-name="project-name"]').val('<option value="0">Загрузка...</option>');
    $.get('api.php?act=address&get=address&address='+encodeURI(address))
    .done(function(data) {api = data;})
    .fail(function() {$('input[object-name="project-name"]').val('<option value="0">Ошибка...</option>');})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {
            $('select[object-name="project-name"]').val('<option value="0">Ошибка...</option>');
            return false;
        }
        if(api.data.length < 1){$('select[object-name="project-name"]').val('<option value="0">Нет данных</option>'); return false;}
        $('select[object-name="project-name"]').html('');
        for(var i=0;i<api.data.length;i++)
            $('select[object-name="project-name"]').append('<option value="'+api.data[0].cable+'">'+api.data[0].cable+'</option>');

    });
}
function addRowWork(id)
{
    id = $('a[param-id="'+id+'"]').attr('param-id');
    var text = $('a[param-id="'+id+'"]').attr('param-text');
    var type = $('a[param-id="'+id+'"]').attr('param-type');
    var item_id = $('a[param-id="'+id+'"]').attr('param-item-id');
    var retail = $('a[param-id="'+id+'"]').attr('param-retail');
    var min = $('a[param-id="'+id+'"]').attr('param-min');
    var eid = 0;
    if($('tbody[object-name="ks"] tr').length > 0)
    {
        eid = $('tbody[object-name="ks"] tr').length;
        eid = parseInt($('tbody[object-name="ks"] tr:nth-child('+eid+') td:nth-child(1)').html());
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
    item += '<td><input type="text" class="form-control" style="text-align: center;" onkeyup="workCalcRow(\''+now+'\')" value="0"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" onkeyup="workCalcRow(\''+now+'\')" value="'+retail+'"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="0"/></td>';
    item += '<td style="vertical-align: middle;"><a href="#remove" onclick="workDelRow(\''+now+'\')" title="Удалить запись"><i class="glyphicon glyphicon glyphicon glyphicon-remove"></i></a></td>';
    item +='</tr>';
    $('tbody[object-name="ks"]').append(item);
}
function save_ks()
{

    var date_a = $('input[object-name="date-start"]').val();
    if(date_a == "")
    {
        alert("Не задана дата начала работ");
        return;
    }
    var date_b = $('input[object-name="date-stop"]').val();
    if(date_b == "")
    {
        alert("Не задана дата окончания работ");
        return;
    }
    var price  = $('select[object-name="contact-n"]').val();
    if(price == null)
    {
        alert("Не задан номер договора");
        return;
    }
    var doc_n  = $('input[object-name="document-n"]').val();
    if(doc_n == "")
    {
        alert("Не задана дата окончания работ");
        return;
    }
    var work_name = $('input[object-name="work-name"]').val();
    if(work_name == "")
    {
        alert("Не задано название работ");
        return;
    }
    var project =  $('select[object-name="project-name"]').val();
    if(project == "")
    {
        alert("Не задан номер документа");
    }
    var works = '';
    var material = '';
    var prepayment = $('input[object-name="prepayment"]').val();
    for(var i=1;i<=$('tbody[object-name="ks"] tr').length;i++)
    {
        works += $('tbody[object-name="ks"] tr:nth-child('+i+')').attr('item-wid')+';';
        works += $('tbody[object-name="ks"] tr:nth-child('+i+') td:nth-child(5) input').val()+';'
        works += $('tbody[object-name="ks"] tr:nth-child('+i+') td:nth-child(6) input').val()+';'
        works += $('tbody[object-name="ks"] tr:nth-child('+i+') td:nth-child(7) input').val()
        works += "\r\n";
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
    $.post('api.php?act=save_ks&type=drs',
    {
        project:project,
        work_name:work_name,
        doc_n:doc_n,
        price:price,
        date_a:date_a,
        date_b:date_b,
        works:works,
        material:material,
        prepayment:prepayment
    })
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


function addRowWorkEditDrs(i)
{
    id = edit_data['content'][i]['id'];
    var text = edit_data['content'][i]['text'];
    var type = edit_data['content'][i]['type'];
    var item_id = edit_data['content'][i]['item_id'];
    var retail = edit_data['content'][i]['retail'];
    var min = edit_data['content'][i]['min'];
    var eid = 0;
    if($('tbody[object-name="ks"] tr').length > 0)
    {
        eid = $('tbody[object-name="ks"] tr').length;
        eid = parseInt($('tbody[object-name="ks"] tr:nth-child('+eid+') td:nth-child(1)').html());
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
    item += '<td><input type="text" class="form-control" style="text-align: center;" onkeyup="workCalcRow(\''+now+'\')" value="'+edit_data['content'][i]['value']+'"/></td>';
    //workCalcRow(now);
    item += '<td><input type="text" class="form-control" style="text-align: center;" onkeyup="workCalcRow(\''+now+'\')" value="'+retail+'"/></td>';
    item += '<td><input type="text" class="form-control" style="text-align: center;" value="0"/></td>';
    item += '<td style="vertical-align: middle;"><a href="#remove" onclick="workDelRow(\''+now+'\')" title="Удалить запись"><i class="glyphicon glyphicon glyphicon glyphicon-remove"></i></a></td>';
    item +='</tr>';
    $('tbody[object-name="ks"]').append(item);
    workCalcRow(now);
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