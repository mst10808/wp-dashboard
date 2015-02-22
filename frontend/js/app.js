function loadUpdateData (site,env) {
  $.ajax({
    type: "GET",
    url: site,
  }).done(function(response){
    var table;
    for (site in response){

      //Check if upgrade required
      if (response[site].core.action == 'upgrade') {
        upgraderequired = true;
      } else {
        upgraderequired = false;
      }

      table += '<tr><td><b>'+site+'</b></td>';

      //Set Update status
      if (upgraderequired) {
        table += '<td><span class="label label-danger">Update Required</span></td>';
      } else {
        table += '<td><span class="label label-success">Update Status OK</span></td>';
      }

      //Include new avail version if there is one
      if (upgraderequired) {
        table += '<td>New Version '+response[site].core.new+' available<br />Current: '+response[site].core.current+'</td>';
      } else {
        table += '<td>Version '+response[site].core.current+' is current</td>';
      }

      //List of plugins to update
      table += '<td>';
      for (plugin in response[site].plugins){
        table += plugin+': '+response[site].plugins[plugin]+'<br />';
      }
      table += '</td></tr>';

    }
    $("#update-data-"+env).append(table);
  })
}

loadUpdateData('https://example.com/wpupdates','prod');
