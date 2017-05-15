function EVASukhotinVowelIdentifier(tokens, morphemeDelimiter) {
  // input tokens
	this.tokens = tokens;
  // morpheme delimiter
  this.morphemeDelimiter = morphemeDelimiter;
  // convert tokens to list of morphemes ignoring spaces
  this.continuousMorphemes = tokens.join(this.morphemeDelimiter).split(this.morphemeDelimiter);
 
  // list of unique morphemes in input tokens
  this.uniqueMorphemes = {};
  this.getUniqueMorphemes();
  this.uniqueMorphemeList = Object.keys(this.uniqueMorphemes)

  // initialise empty arrays - morpheme = morpheme
	this.morphemeAdjacencyFrequencyMatrix = [];
	this.morphemeRowCounts = [];
  
  // output list of 'vowels'
	this.identifiedVowels = [];
  // html trace of output
  this.htmlTrace;
  
}

EVASukhotinVowelIdentifier.prototype.getUniqueMorphemes = function() {
  // iterate tokens
  for(var i=0; i<this.tokens.length; i++) {
    var token = tokens[i];
    var tokenMorphemes = token.split(this.morphemeDelimiter);
    // iterate morphemes
    for(var j=0; j<tokenMorphemes.length; j++) {
      // add to object if not exists
      if (!this.uniqueMorphemes[tokenMorphemes[j]]) {
        this.uniqueMorphemes[tokenMorphemes[j]] = true;
      }
    }
  }
}

EVASukhotinVowelIdentifier.prototype.runAlgorithm = function(tableStyle) {
	var html = '';
	var checking;
	var vowel;
	
	// algorithm
	// step 0 - initialise laf (morpheme Adjacency Frequency matrix)
	this.initialiseLaf();

	
	// step 1 - populate laf with adjacent morpheme frequency counts
	this.populateLaf();

	// step 2 - blank main diagonal (top left to bottom right) of laf
	this.blankLafTLBRDiagonal();
	// append to trace
	html += '<h4>morpheme Adjacency Frequency table (with zeroed diagonal)</h4>';
	html += this.lafToTable(tableStyle).outerHTML;
	html += '<p>';
	
	// step 3 - update row counts from laf
	this.updateRowCountsFromLaf();
	// append to html trace
	html += '<h4>Initial state of morpheme row counts</h4>';
	html += this.rowCountsToTable(tableStyle).outerHTML;
	html += '<p>';

	// loop on vowel identification procedure
	checking = true;
	while(checking) {
		vowel = this.getVowelFromRowCountMax();
		if (vowel != '') {
			this.identifiedVowels.push(vowel);
			// append to html trace
			html += 'Vowel identified: ' + vowel;
			html += '<p>';
			
		} else {
			checking = false;
			// append to html trace
			html += 'No more vowels identified';
			html += '<p>';
		}
		
		// if found a vowel, update row counts
		// and restart loop to check for next vowel
		if (checking) {
			this.updateRowCountsAfterVowelIdentification(vowel);
			// append to html trace
			html += '<h4>Next state of morpheme row counts</h4>';
			html += this.rowCountsToTable(tableStyle).outerHTML;
			html += '<p>';
			
		}
	}

	// append to html trace
	html += '<h4>Vowels identified: ' + this.identifiedVowels.join(', ') + '</h4>';
	html += '<p>';
  
	// return html
	this.htmlTrace = html;
}

EVASukhotinVowelIdentifier.prototype.initialiseLaf = function() {

	var morpheme, morpheme2;
	
	for(var i = 0; i < this.uniqueMorphemeList.length; i++) {
		morpheme = this.uniqueMorphemeList[i];
		this.morphemeAdjacencyFrequencyMatrix[morpheme] = [];
		for(var j = 0; j < this.uniqueMorphemeList.length; j++) {
			morpheme2 = this.uniqueMorphemeList[j];
			this.morphemeAdjacencyFrequencyMatrix[morpheme][morpheme2] = 0;
		}
	}
}

EVASukhotinVowelIdentifier.prototype.populateLaf = function() {

	var morpheme, priorMorpheme, nextMorpheme;
	
	for(var i = 1; i < this.continuousMorphemes.length - 1; i++) {
		// get adjacent morphemes
    morpheme = this.continuousMorphemes[i];
    priorMorpheme = this.continuousMorphemes[i - 1];
    nextMorpheme = this.continuousMorphemes[i + 1];
        
		// increment laf
		this.morphemeAdjacencyFrequencyMatrix[morpheme][priorMorpheme] += 1;
		this.morphemeAdjacencyFrequencyMatrix[morpheme][nextMorpheme] += 1;
	}
  
}

EVASukhotinVowelIdentifier.prototype.blankLafTLBRDiagonal = function () {
	
	var morpheme;
	
	for(var i = 0; i < this.uniqueMorphemeList.length; i++) {
		morpheme = this.uniqueMorphemeList[i];
		this.morphemeAdjacencyFrequencyMatrix[morpheme][morpheme] = 0;
	}	
}

EVASukhotinVowelIdentifier.prototype.updateRowCountsFromLaf = function () {
	
	var rowCount;
	var morpheme, morpheme2;
	
	for(var i = 0; i < this.uniqueMorphemeList.length; i++) {
		rowCount = 0;
		morpheme = this.uniqueMorphemeList[i];
		for(j = 0; j < this.uniqueMorphemeList.length; j++) {
			morpheme2 = this.uniqueMorphemeList[j];
			rowCount += this.morphemeAdjacencyFrequencyMatrix[morpheme][morpheme2];
		}
		this.morphemeRowCounts[morpheme] = rowCount;
	}
}

EVASukhotinVowelIdentifier.prototype.getVowelFromRowCountMax = function () {
	var vowel = '';
	var maxCount;
	var morpheme;
	var test1, test2, test3;
	
	maxCount = 0;
	for(var i = 0; i < this.uniqueMorphemeList.length; i++) {
		morpheme = this.uniqueMorphemeList[i];
		test1 = this.morphemeRowCounts[morpheme] > maxCount;
		test2 = this.morphemeRowCounts[morpheme] > 0;
		test3 = this.identifiedVowels.indexOf(morpheme) == -1;
		
		if ( test1 && test2 && test3 ) {
			vowel = morpheme;
			maxCount = this.morphemeRowCounts[morpheme];
		}		
	}
	
	return vowel;
}

EVASukhotinVowelIdentifier.prototype.updateRowCountsAfterVowelIdentification = function (lastVowel) {
	var morpheme;
	
	for(var i = 0; i < this.uniqueMorphemeList.length; i++) {
		morpheme = this.uniqueMorphemeList[i];
		if (morpheme != lastVowel ) {
			// deduct morpheme/vowel value*2 from laf from row_counts
			this.morphemeRowCounts[morpheme] -= (this.morphemeAdjacencyFrequencyMatrix[morpheme][lastVowel] * 2)
		}
	}	
}

EVASukhotinVowelIdentifier.prototype.lafToTable = function(tableStyle) {
	// output laf to table
	var cellValue;
	var morpheme, morpheme2;
	var table = document.createElement('table');
	var tableBody = document.createElement('tbody');
	var row, cell;
	
	// style table if parameter is not empty string
	if (tableStyle != '') {
		table.setAttribute('class', tableStyle);	
	}
	
	for(var i = -1; i < this.uniqueMorphemeList.length; i++) {
		// create row
		row = document.createElement('tr');
		// -1 for index cells on rows/ columns
		for(var j = -1; j < this.uniqueMorphemeList.length; j++) {
			
			if (i == -1 && j == -1 ) {
				// null cell
				cell = document.createElement('th');
			} else if (i > -1 && j == -1) {
				// row header
				cell = document.createElement('th');
				cell.appendChild(document.createTextNode(this.uniqueMorphemeList[i]));
			} else if (i == -1 && j > -1) {
				// column header
				cell = document.createElement('th');
				cell.appendChild(document.createTextNode(this.uniqueMorphemeList[j]));
			} else {
				// data
				cell = document.createElement('td');
				morpheme = this.uniqueMorphemeList[i];
				morpheme2 = this.uniqueMorphemeList[j];
				cellValue = this.morphemeAdjacencyFrequencyMatrix[morpheme][morpheme2];
				cell.appendChild(document.createTextNode(cellValue));
			}	
			
			// append cell to row
			row.appendChild(cell);
		}

		// append row to body
		tableBody.appendChild(row);
	}
	
	// append body to table
	table.appendChild(tableBody);
	
	return table;
}

EVASukhotinVowelIdentifier.prototype.rowCountsToTable = function(tableStyle) {
	// output row counts to table
	var i, morpheme;
	var table = document.createElement('table');
	var tableBody = document.createElement('tbody');
	var row, cell1, cell2;

	// style table if parameter is not empty string
	if (tableStyle != '') {
		table.setAttribute('class', tableStyle);	
	}
	
	// header
	row = document.createElement('tr');
	cell1 = document.createElement('th');
	cell1.appendChild(document.createTextNode('morpheme'));
	cell2 = document.createElement('th');
	cell2.appendChild(document.createTextNode('Count'));
	row.appendChild(cell1);
	row.appendChild(cell2);
	tableBody.appendChild(row);
	
	for(i = 0; i < this.uniqueMorphemeList.length; i++) {

		// create row
		row = document.createElement('tr');

		// append morpheme
		cell1 = document.createElement('td');
		cell1.appendChild(document.createTextNode(this.uniqueMorphemeList[i]));
		
		// append count
		cell2 = document.createElement('td');
		cell2.appendChild(document.createTextNode(this.morphemeRowCounts[this.uniqueMorphemeList[i]]));

		// append cell to row
		row.appendChild(cell1);
		row.appendChild(cell2);

		// append row to body
		tableBody.appendChild(row);
	}
	
	// append body to table
	table.appendChild(tableBody);
	
	return table;
}