/*
 * Модуль  blankEntityModule, подключается на странице настроек плагина.
 */

var blankEntityModule = (function () {

    return {
        lang             : [], // локаль плагина
        init             : function () {

            // установка локали плагина
            admin.ajaxRequest({
                                  mguniqueurl: "action/seLocalesToPlug",
                                  pluginName : 'pozvonim'
                              },
                              function (response) {
                                  blankEntityModule.lang = response.data;
                              }
            );

            $('.admin-center').on('click', '.section-pozvonim .base-setting-save', function () {

                var data = {};
                $('.list-option input,.list-option textarea, .list-option select').each(function () {
                    data[ $(this).attr('name') ] = $(this).val();
                });

                data.nameEntity = $(".base-settings input[name=nameEntity]").val();

                admin.ajaxRequest({ mguniqueurl: "action/saveBaseOption", pluginHandler: 'pozvonim', data: data },
                                  function (response) {
                                      if (response.msg == 'reload')
                                      {
                                          location.reload();
                                      } else
                                      {
                                          admin.indication(response.status, response.msg);
                                      }
                                  }
                );

            });

            $('.admin-center').on('click', '#restoreToken', function () {
                var email = $('#email').val();

                if (!/^[a-z0-9\-]+?@[a-z0-9\-]+?\.[a-z]{2,}$/i.test(email))
                {
                    admin.indication(-1, $(this).data('rmsg'));
                    return false;
                }
                if (confirm($(this).data('emsg').replace('#email', email)))
                {
                    admin.ajaxRequest({
                                          mguniqueurl  : "action/restoreTokenToEmail",
                                          pluginHandler: 'pozvonim',
                                          data         : {
                                              email     : email,
                                              nameEntity: $(".base-settings input[name=nameEntity]").val()
                                          }
                                      },
                                      function (response) {
                                          if (response.msg == 'reload')
                                          {
                                              location.reload();
                                          } else
                                          {
                                              admin.indication(response.status, response.msg);
                                          }
                                      }
                    );
                }

            });

        }
    }
})();

blankEntityModule.init();
admin.sortable('.entity-table-tbody', 'pozvonim');