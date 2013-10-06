$(document).ready(function(){
    getContactorList();
});
function getCotractorData(e)
{//catalog_contractor_get_data
    $('div[object-name="contractor-list"] a').removeClass('active');
    $(e).addClass('active');
    var api = '';
    $.get('api.php?act=catalog_contractor_get_data&id='+$(e).attr('object-id'))
    .done(function(data) {api = data;})
    .fail(function() {return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {return false;}
        $('em[object-name="name"]').html(api.data.name);
        $('em[object-name="cciso"]').html(api.data.cciso);
        $('em[object-name="riso"]').html(api.data.riso);
        $('em[object-name="sroca"]').html(api.data.sroca);
        $('em[object-name="srocm"]').html(api.data.srocm);
        $('em[object-name="director"]').html(api.data.director);
        $('em[object-name="accountant"]').html(api.data.accountant);
        $('em[object-name="legal_address"]').html(api.data.legal_address);
        $('em[object-name="actual_address"]').html(api.data.actual_address);
        $('em[object-name="pastal_address"]').html(api.data.pastal_address);
        $('em[object-name="inn"]').html(api.data.inn);
        $('em[object-name="kpp"]').html(api.data.kpp);
        $('em[object-name="ogrn"]').html(api.data.ogrn);
        $('em[object-name="okpo"]').html(api.data.okpo);
        $('em[object-name="okvd"]').html(api.data.okvd);
        $('em[object-name="r_s"]').html(api.data.r_s);
        $('em[object-name="in"]').html(api.data.in);
        $('em[object-name="k_s"]').html(api.data.k_s);
        $('em[object-name="bik"]').html(api.data.bik);
        $('em[object-name="phone"]').html(api.data.phone);
        $('em[object-name="fax"]').html(api.data.fax);
        $('em[object-name="mail"]').html(api.data.mail);
        $('button[object-name="btncupdate"]').attr('object-id',api.data.id);
        $('button[object-name="btncdelete"]').attr('object-id',api.data.id);
    });
}
function updateContractor(e)
{
    var api = '';
    $(e).html('Загружаю...');
    $.get('api.php?act=catalog_contractor_get_data&id='+$(e).attr('object-id'))
    .done(function(data) {api = data;})
    .fail(function() {$(e).html('Редактирвать');return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {$(e).html('Редактирвать');return false;}
         $('form[object-name="updateContractorForm"] input[name="id"]').val(api.data.id);
        $('form[object-name="updateContractorForm"] input[name="name"]').val(api.data.name);
        $('form[object-name="updateContractorForm"] input[name="cciso"]').val(api.data.cciso);
        $('form[object-name="updateContractorForm"] input[name="riso"]').val(api.data.riso);
        $('form[object-name="updateContractorForm"] input[name="sroca"]').val(api.data.sroca);
        $('form[object-name="updateContractorForm"] input[name="srocm"]').val(api.data.srocm);
        $('form[object-name="updateContractorForm"] input[name="director"]').val(api.data.director);
        $('form[object-name="updateContractorForm"] input[name="accountant"]').val(api.data.accountant);
        $('form[object-name="updateContractorForm"] input[name="legal_address"]').val(api.data.legal_address);
        $('form[object-name="updateContractorForm"] input[name="actual_address"]').val(api.data.actual_address);
        $('form[object-name="updateContractorForm"] input[name="pastal_address"]').val(api.data.pastal_address);
        $('form[object-name="updateContractorForm"] input[name="inn"]').val(api.data.inn);
        $('form[object-name="updateContractorForm"] input[name="kpp"]').val(api.data.kpp);
        $('form[object-name="updateContractorForm"] input[name="ogrn"]').val(api.data.ogrn);
        $('form[object-name="updateContractorForm"] input[name="okpo"]').val(api.data.okpo);
        $('form[object-name="updateContractorForm"] input[name="okvd"]').val(api.data.okvd);
        $('form[object-name="updateContractorForm"] input[name="r_s"]').val(api.data.r_s);
        $('form[object-name="updateContractorForm"] input[name="in"]').val(api.data.in);
        $('form[object-name="updateContractorForm"] input[name="k_s"]').val(api.data.k_s);
        $('form[object-name="updateContractorForm"] input[name="bik"]').val(api.data.bik);
        $('form[object-name="updateContractorForm"] input[name="phone"]').val(api.data.phone);
        $('form[object-name="updateContractorForm"] input[name="fax"]').val(api.data.fax);
        $('form[object-name="updateContractorForm"] input[name="mail"]').val(api.data.mail);
        $('#modal_update_contract').modal('show');
        $(e).html('Редактирвать');
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
        if(api.data.length < 1){$('div[object-name="contractor-list"]').html('<a href="#" class="list-group-item"><h4 class="list-group-item-heading">Подрядчиков не обнаружено</h4><p class="list-group-item-text">Вы еще не создали ни одго подрядчика</p></a>'); return false;}
        var item = '';
        $('div[object-name="contractor-list"]').html('');
        for(var i=0;i<api.data.length;i++)
        {
            item = '<a href="#" object-id="'+api.data[i].id+'" class="list-group-item" onclick="getCotractorData(this)"><h4 class="list-group-item-heading">'+api.data[i].name+'</h4>';
            item += '<p class="list-group-item-text">Телефон: '+api.data[i].phone+' / E-mail: '+api.data[i].mail+'</p></a>';
            $('div[object-name="contractor-list"]').append(item);
        }
    });
}
function newContractor()
{
    $('#modal_new_contract').modal('show');
}
function saveNewContractor()
{
    var api = '';
    $.post('api.php?act=catalog_contractor_add',$('form[object-name="newContractorForm"]').serialize())
    .done(function(data) {api = data;})
    .fail(function() {alert('Не удалось создать подрядчика');return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {
            if(api.code == '404')
            {alert('Такое название уже используется, попропуйте сменить название');}
            else
            {alert('Не удалось создать подрядчика');}
            return false;
        }
        getContactorList();
        alert('Новый подрядчик создан');
         $('#modal_new_contract').modal('hide');
    });
}
function saveUpdateContractor()
{
     var api = '';
    $.post('api.php?act=catalog_contractor_update',$('form[object-name="updateContractorForm"]').serialize())
    .done(function(data) {api = data;})
    .fail(function() {alert('Не удалось сохранить изменения');return false;})
    .success(function(){
        console.log(api);
        api = $.parseJSON(api);
        if(api.code != '200')
        {
           alert('Не удалось сохранить изменения');
            return false;
        }
        getContactorList();
        alert('Изменения сохранены');
        $('#modal_update_contract').modal('hide');
    });
}
function deleteContractor(e)
{
    var api = '';
    $(e).html('Удаляю...');
    $.get('api.php?act=catalog_contractor_delete&id='+$(e).attr('object-id'))
    .done(function(data) {api = data;})
    .fail(function() {$(e).html('Редактирвать');return false;})
    .success(function(){
        api = $.parseJSON(api);
        if(api.code != '200')
        {$(e).html('Удалить');return false;}
        getContactorList();
        alert('Подрядчик удален');
        $(e).html('Удалить');
    });
}