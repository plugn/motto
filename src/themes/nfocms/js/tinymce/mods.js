{{TPL:cfgPrice0}}
        tinyMCE.init({
                mode : "textareas",
                theme : "advanced",
                plugins : "devkit,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
                theme_advanced_buttons1_add_before : "save,newdocument,separator",
                theme_advanced_buttons1_add : "fontselect,fontsizeselect",
                theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,separator,forecolor,backcolor,advsearchreplace",
                theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
                theme_advanced_buttons3_add_before : "tablecontrols,separator",
                theme_advanced_buttons3_add : "emotions,iespell,media,advhr,separator,print,separator,ltr,rtl,separator,fullscreen",
                theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_path_location : "bottom",
                content_css : "example_full.css",
            plugin_insertdate_dateFormat : "%Y-%m-%d",
            plugin_insertdate_timeFormat : "%H:%M:%S",
                extended_valid_elements : "hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
                external_link_list_url : "example_link_list.js",
                external_image_list_url : "example_image_list.js",
                flash_external_list_url : "example_flash_list.js",
                media_external_list_url : "example_media_list.js",
                file_browser_callback : "fileBrowserCallBack",
                theme_advanced_resize_horizontal : false,
                theme_advanced_resizing : true,
                nonbreaking_force_tab : true,
                apply_source_formatting : true
        });

        function fileBrowserCallBack(field_name, url, type, win) {
                // This is where you insert your custom filebrowser logic
                alert("Example of filebrowser callback: field_name: " + field_name + ", url: " + url + ", type: " + type);

                // Insert new URL, this would normaly be done in a popup
                win.document.forms[0].elements[field_name].value = "someurl.htm";
        }

{{/TPL:cfgPrice0}}

{{TPL:cfgPrice1}}

  tinyMCE.init( document.cfgMCE = {
          mode : "textareas",
          theme : "advanced",
          editor_selector : "mceEditor",
          plugins : "table,save,advhr,advimage,advlink,preview,zoom,searchreplace,contextmenu",
          theme_advanced_buttons1_add_before : "save,newdocument,separator",
          theme_advanced_buttons1_add : "fontselect,fontsizeselect",
          theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor",
          theme_advanced_buttons2_add_before: "cut,copy,paste,separator",
          theme_advanced_buttons3_add_before : "tablecontrols,separator",
          theme_advanced_buttons3_add : "advhr",
          theme_advanced_toolbar_location : "top",
          theme_advanced_toolbar_align : "left",
          theme_advanced_path_location : "bottom",
          extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
          external_link_list_url : "example_data/example_link_list.js",
          external_image_list_url : "example_data/example_image_list.js" // flash_external_list_url : "example_data/example_flash_list.js"
  });

{{/TPL:cfgPrice1}}

{{TPL:cfgPrice}}

  tinyMCE.init( document.cfgMCE = {
          mode : "textareas",
          theme : "advanced",
          editor_selector : "mceEditor",
          plugins : "table,save,advlink,searchreplace,contextmenu",
          theme_advanced_buttons1_add_before : "save,newdocument,separator",
          theme_advanced_buttons2_add : "forecolor,backcolor",
          theme_advanced_buttons2_add_before: "cut,copy,paste,separator",
          theme_advanced_buttons3_add_before : "tablecontrols,separator",
          theme_advanced_toolbar_location : "top",
          theme_advanced_toolbar_align : "left",
          theme_advanced_path_location : "bottom",
          extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
          external_link_list_url : "example_data/example_link_list.js",
          external_image_list_url : "example_data/example_image_list.js" // flash_external_list_url : "example_data/example_flash_list.js"
  });

{{/TPL:cfgPrice}}