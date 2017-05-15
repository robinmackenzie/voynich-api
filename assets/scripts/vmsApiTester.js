var urlBase;
var columns = [];
var selectedStatus = [];

// set base url
$(document).ready(function() {
  // get base url
  var pathName = location.pathname;
  urlBase = location.protocol + '//';
  urlBase += location.hostname;
  items = pathName.split('/');
  urlBase += items.slice(0, items.length - 2).join('/');
  urlBase += '/api/v1/';
  $('#inputBaseUrl').val(urlBase);
  
  // add a button per column 
  $.get(urlBase + '/columns', function(data) {
    columns = Object.keys(data.values);
    $.each(columns, function(key, value) {
      var button = '<button class="btn btn-default btn-xs" id="button"' + value + '>' + value + '</button>';
      $('#panelColumnButtons').append(button);            
      // add columns to dropdown
      var item = '<li><a href="#">' + value + '</a></li>';
      $('#listColumnOptions').append(item);
    });
    // set selected status
    $.each(columns, function(k) {selectedStatus.push(false);});
  });
});

// listener for column filter buttons
$('#panelColumnButtons').on('click', 'button', function() {
  var button = $(this);
  var columnName = button.html();
  var index = columns.indexOf(columnName);
  
  // toggle status and update class
  if (selectedStatus[index] == false) {
    selectedStatus[index] = true;
    button.attr('class', 'btn btn-success btn-xs');
  } else {
    selectedStatus[index] = false;
    button.attr('class', 'btn btn-default btn-xs');
  };
  
  // update input
  var columnList = columns.filter(function(e, i, a) {return selectedStatus[i];});
  $('#inputColumnFilter').val(columnList.join(','));        
  
});

// handle API GET for morphemes
$('#buttonGetMorphemes, #buttonGetUniqueMorphemes').on('click', function() {
  var morphemes, url, filters;
  
  // clear output
  clearOutputs();

  // base url
  var sourceButtonId = $(this)[0].id;
  if(sourceButtonId == 'buttonGetMorphemes') {
    url = $('#inputBaseUrl').val() + 'morphemes/';
  } else {
    url = $('#inputBaseUrl').val() + 'uniqueMorphemes/';
  }
  
  // check morpheme list
  morphemes = $('#inputMorphemes').val().trim();
  if (morphemes == '') {
    showAlert('To retrieve morphemized tokens enter a comma delimited list of morphemes');
    return;
  }
  url += morphemes;
  
  // check optional columns for non-unique morphemes
  if(sourceButtonId == 'buttonGetMorphemes') {
    filters = $('#inputColumnFilter').val().trim();
    if (filters !== '') {
      url += '/' + filters;
    }
  }
  
  // output final url
  var finalUrl = url + '?' + $('#inputQueryParameters').val();
  $('#inputFinalUrl').val(finalUrl);
  
  // do GET
  $.ajax({
    'url': url,
    'type': 'GET',
    'data': $('#inputQueryParameters').val(),
    'success': function(response) {
      setOutput(response, 'success', $('#checkPretty').prop('checked'));
    },
    'error': function(xhr, status, error) {
      setOutput(xhr.responseText, 'warning', $('#checkPretty').prop('checked'));
    }
  });
});

// handle API GET for tokens
$('#buttonGetTokens, #buttonGetUniqueTokens').on('click', function() {
  var url, filters;
  
  // clear output
  clearOutputs();

  // base url
  var sourceButtonId = $(this)[0].id;
  if(sourceButtonId == 'buttonGetTokens') {
    url = $('#inputBaseUrl').val() + 'tokens';
  } else {
    url = $('#inputBaseUrl').val() + 'uniqueTokens';    
  }
  
  // check optional columns for non-unique tokens
  if(sourceButtonId == 'buttonGetTokens') {  
    filters = $('#inputColumnFilter').val().trim();
    if (filters !== '') {
      url += '/' + filters;
    }
  }
  
  // output final url
  var finalUrl = url + '?' + $('#inputQueryParameters').val();
  $('#inputFinalUrl').val(finalUrl);
  
  // do GET
  $.ajax({
    'url': url,
    'type': 'GET',
    'data': $('#inputQueryParameters').val(),
    'success': function(response) {
      setOutput(response, 'success', $('#checkPretty').prop('checked'));
    },
    'error': function(xhr, status, error) {
      setOutput(xhr.responseText, 'warning', $('#checkPretty').prop('checked'));
    }
  });
});

// handle API GET for metadata
$('#listColumnOptions').on('click', 'a', function(item) {
  var url, column;
  
  // clear output
  clearOutputs();
  
  // create url
  url = $('#inputBaseUrl').val() + 'meta/' + $(this).html();
  
  // output final url
  $('#inputFinalUrl').val(url);
  
  // do GET
  $.ajax({
    'url': url,
    'type': 'GET',
    'success': function(response) {
      setOutput(response.values, 'success', $('#checkPretty').prop('checked'));
    },
    'error': function(xhr, status, error) {
      setOutput(xhr.responseText, 'warning', $('#checkPretty').prop('checked'));
    }
  }); 
});

function clearOutputs() {  
  $('#inputFinalUrl').val('');
  $('#containerOutput').html('');
}

function setOutput(content, level, isPretty) {
  // update container
  if (isPretty) {
    $('#containerOutput').append('<pre>' + prettyPrint(content) + '</pre>'); 
  } else {
    $('#containerOutput').append('<pre>' + JSON.stringify(content) + '</pre>');  
  }
  // format panel
  $('#panelOutput').attr('class', 'panel panel-' + level);
}

function showAlert(message) {
  $('#modalMessageContent').html('<p>' + message + '</p>');
  $('#modalMessage').modal('show');
}

function prettyPrint(uglyObj) {
	//http://stackoverflow.com/questions/4810841/how-can-i-pretty-print-json-using-javascript
	var prettyJson = JSON.stringify(uglyObj, undefined, 4);
  prettyJson = prettyJson.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  return prettyJson.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
    var cls = 'number';
    if (/^"/.test(match)) {
      if (/:$/.test(match)) {
        cls = 'key';
      } else {
        cls = 'string';
      }
    } else if (/true|false/.test(match)) {
      cls = 'boolean';
    } else if (/null/.test(match)) {
      cls = 'null';
    }
    return '<span class="' + cls + '">' + match + '</span>';
  });
}