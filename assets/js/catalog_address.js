$(document).ready(function() {
    getAtsList('ats');
    $('select[object-name="ats"]').change(function(){
        getClusterList('cluster',$(this).val());
        $('ul[object-name="address"]').html('');
    });
    $('select[object-name="cluster"]').change(function(){
        getAddressByAtsCluster($('select[object-name="ats"]').val(),$(this).val());
    });
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
    $('select[object-name="address"]').html('<li><a>Загрузка...</a></li>');
    $.get('api.php?act=address&get=address-by-ats-cluster&ats='+encodeURI(ats)+'&cluster='+encodeURI(cluster))
    .done(function(data) {api = data;})
    .fail(function() {$('ul[object-name="address"]').html('<li><a>Ошибка...</a></li>');})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {
            $('select[object-name="address"]').html('<li><a>Ошибка...</a></li>');
            return false;
        }
        if(api.data.length < 1){$('ul[object-name="address"]').html('<li><a>Нет данных</a></li>'); return false;}
        $('ul[object-name="address"]').html('');
        for(var i=0;i<api.data.length;i++)
        {
            $('ul[object-name="address"]').append('<li><a href="#ats-'+ats+'-cluster-'+cluster+'-address-'+api.data[i].address+'" data-toggle="tab" onclick="getAddressData(\''+api.data[i].address+'\')">'+api.data[i].address+'</a></li>');
        }
    });
}
function getAddressData(address)
{
    var api = false;
    $('input[object-name="project-name"]').val('Загрузка...');
    $.get('api.php?act=address&get=address&address='+encodeURI(address))
    .done(function(data) {api = data;})
    .fail(function() {$('input[object-name="project-name"]').val('Ошибка...');})
    .success(function(){
        console.log(api);
        api = $.parseJSON(api);
        if(api.code != '200')
        {
            alert('Ошибка получения данных для адреса: '+$('select[object-name="address"]').val());
            return false;
        }
        if(api.data.length < 1){alert('Нет данных для адреса: '+$('select[object-name="address"]').val()); return false;}
        $('div[object-name="address-text"]').html(address);
        $('em[object-name="address-cable"]').html(api.data[0].cable);
        $('em[object-name="address-client"]').html(api.data[0].clients);
        $('em[object-name="address-port"]').html(api.data[0].port);
        $('em[object-name="address-asrz"]').html(api.data[0].asrz);
        $('em[object-name="address-оjrsha"]').html(api.data[0].type);
    });
}

