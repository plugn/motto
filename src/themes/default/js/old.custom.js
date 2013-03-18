/*
 * @description  custom project page handlers
 * @author       Max L. Dolgov      <bananafishbone at gmail dot com>
 *
 */


function wgtEnter(el) {
  if (parseInt(el.value)!=el.value) {
      el.style.color='white';
      el.style.backgroundColor='red';
      return false;
  }

  el.style.color='black';
  el.style.backgroundColor='white';
  return true;

}

function wgtCommit(el,isValid) {
    if (isValid)        {
      (new mAjax()).send(  // RPC
            {   prmkey:el.id,
                prmval:el.value,
                URI : 'http://'+location.host+"/ui.server/widget_rpc/",
                pgsBarId: "pgsbar",
                rspBarId: "cartSpySum"
            }             );
       /* then update cart window
        spySum = document.getElementById("cartSpySum");
        spySum.value=(spyVal);      */
    }
}


function chkToggle ( id ) {
    document.getElementById(id).click();
}

/*** tec route : rpc-based UI constructor. ***/
function tecCB1 ( respTxt, cfg ) { /*** callback. it will be called from mAjax object when response complete ***/
    RspId = cfg.RspId;    // data_id
    document.getElementById(RspId).innerHTML = '';
    document.getElementById(RspId).innerHTML = respTxt;
    // eval ( 'document.phaseof_'+cfg.params.data_id+'+=1;' );eval('alert(document.phaseof_'+cfg.params.data_id+');');
}

function tecExec( data_id, RspId, alias, prm ) {
    (new mAjax()).send(      // pay attention: this mAjax source differs with one used higher
            {   prm       : prm,
                URI       : 'http://'+location.host+'/ui.server/tecrpc/'+alias+'/'+data_id,
                RspId     : RspId,
                pgsBarId  : 'pgsbar',
                rspCallBk : 'tecCB1'
            } );
}