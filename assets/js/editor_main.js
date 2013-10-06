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
        $('select[object-name="'+object+'"]').html('<option value="0">---</option>');
        for(var i=0;i<api.data.length;i++)
        {
            $('select[object-name="'+object+'"]').append('<option value="'+api.data[i]+'">'+api.data[i]+'</option>');
        }
            try
            {
                $("#ats option[value='"+edit_data['ats']+"']").attr("selected","selected");
                $("#customer option[value='"+edit_data['contract']['customer']['id']+"']").attr("selected","selected");
                $("#contractor option[value='"+edit_data['contract']['contactor']['id']+"']").attr("selected","selected");
                getClusterList('cluster',$('select[object-name="ats"]').val());
                getPriceList(ks_type);
                $("#contr_num").val(edit_data['number']);
                $("input[object-name='date-start']").val(edit_data['date_start']);
                $("input[object-name='date-stop']").val(edit_data['date_stop']);
                $("input[object-name='prepayment']").val(edit_data['prepayment']);

                if(ks_type == "drs")
                for(i = 0; i < edit_data['content'].length;i++)
                {
                    addRowWorkEditDrs(i);
                }
                for(i = 0; i < edit_data['material'].length;i++)
                {
                    if(ks_type == "drs")
                    {
                        materialAddRowEdit(i);
                    }

                }
            }
            catch(exc)
            {
                console.log("getAts exception");
            }

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
        $('select[object-name="'+object+'"]').html('<option value="0">---</option>');
        for(var i=0;i<api.data.length;i++)
        {
            $('select[object-name="'+object+'"]').append('<option value="'+api.data[i].cluster+'">'+api.data[i].cluster+'</option>');
        }
            if (edit_id != undefined)
            {
                $("#cluster option[value='"+edit_data['cluster']+"']").attr("selected","selected");
                getAddressByAtsCluster('address',$('select[object-name="ats"]').val(),$('select[object-name="cluster"]').val());

            }
    });
}
function getAddressByAtsCluster(object,ats,cluster)
{
    var api = false;
    $('select[object-name="'+object+'"]').html('<option value="0">Загрузка...</option>');
    $.get('api.php?act=address&get=address-by-ats-cluster&ats='+encodeURI(ats)+'&cluster='+encodeURI(cluster))
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
        $('select[object-name="'+object+'"]').html('<option value="0">---</option>');
        for(var i=0;i<api.data.length;i++)
        {
            $('select[object-name="'+object+'"]').append('<option value="'+api.data[i].address+'">'+api.data[i].address+'</option>');
        }
            if (edit_id != undefined)
            {

                if(ks_type == "drs")
                {

                    $("#address option[value='"+edit_data['address']+"']").attr("selected","selected");
                    getProjectName($('select[object-name="address"]').val());
                }

                if(ks_type == "ms" && edit_data['content'].length>0)
                {
                    console.log("селект");
                    $('select[object-name="address"] option[value="'+edit_data['content'][0]['address']['address']+'"]').attr("selected","selected");
                    selectAddressEdit(0);
                }
/*
                for(i = 0; i < edit_data['content'].length;i++)
                {
                    console.log("address"+i);
                    if(ks_type == "ms")
                    {
                        if ((i == 0) || (edit_data['content'][i]['address']['address'] != edit_data['content'][i-1]['address']['address']) )
                        {
                            $('select[object-name="address"] option[value="'+edit_data['content'][i]['address']['address']+'"]').attr("selected","selected");
                            console.log($('select[object-name="address"] option[value="'+edit_data['content'][i]['address']['address']+'"]'));

                        }
                        else
                        {
                            console.log("тот же адрес");
                            addRowWorkEditMs(i);
                        }
                    }

                }*/


            }
    });
}
function getWorks(parent,depth)
{
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
    if ($('select[object-name="contact-n"]').val() != null)
        get = 'api.php?act=works&parent='+encodeURI(parent)+'&contractor='+encodeURI($('select[object-name="contact-n"]').val());
    else
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
            $('ul[object-name="work-'+depth+'"]').append('<li><a href="#" data-toggle="tab" param-id="'+api.data[i].id+'" param-text="'+api.data[i].text+'" param-type="'+api.data[i].type+'" param-min="'+api.data[i].min+'" param-retail="'+api.data[i].retail+'"  param-item-id="'+api.data[i].item_id+'" oncontextmenu="addRowWork('+api.data[i].id+'); return false;" onclick="getWorks(\''+api.data[i].item_id+'\','+(depth+1)+')">'+api.data[i].text+'</a></li>');
        }
    });
}
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
            $('select[object-name="contactor"]').html('<option value="0">Подрядчиков не обнаружено</option>'); 
            $('select[object-name="customer"]').html('<option value="0">Подрядчиков не обнаружено</option>'); 
            return false;}
        var item = '';
        $('select[object-name="contractor"]').html('<option value="0">---</option>');
        $('select[object-name="customer"]').html('<option value="0">---</option>');
        for(var i=0;i<api.data.length;i++)
        {
            item = '<option value="'+api.data[i].id+'">'+api.data[i].name+'</option>';
            $('select[object-name="customer"]').append(item);
            $('select[object-name="contractor"]').append(item);
        }
    });
}
function getPriceList(type)
{
    $('select[object-name="contact-n"]').html('<option value="0">Загрузка...</option>');
    var api = '';
    $.get('api.php?act=catalog_price_list_get&contractor='+$('select[object-name="contractor"]').val()+'&constomer='+$('select[object-name="customer"]').val()+'&type='+type)
    .done(function(data) {api = data;})
    .fail(function() {return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {return false;}
        if(api.data.length < 1) {$('select[object-name="contact-n"]').html('<option value="0">Нет данных</option>'); return false;}
        $('select[object-name="contact-n"]').html('<option value="0">---</option>');
        for(var i=0;i<api.data.length;i++)
        {
            $('select[object-name="contact-n"]').append('<option value="'+api.data[i].id+'">'+api.data[i].contact_number+'</option>');
        }
            try
            {
                $("#contract option[value='"+edit_data['contract']['id']+"']").attr("selected","selected");
            }
            catch(exc)
            {

            }
    });
}
function getPriceData(id)
{
     var api = '';
    $.get('api.php?act=catalog_price_getdata&id='+id)
    .done(function(data) {api = data;})
    .fail(function() {return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {return false;}
        $('input[object-name="date-start"]').val(api.data.contact_date);
        
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
        $('table[object-name="price_content"]').html('');
        for(var i=0;i<api.data.length;i++)
        {
            item = '<tr price-el-id="'+api.data[i].work.id+'">';
            item += '<td>'+api.data[i].retail+'</td>';
            item += '</tr>';
            $('table[object-name="price_content"]').append(item);
        }
    });
}