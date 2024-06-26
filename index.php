<?php

$configContents = file_get_contents("./config.json");
$config = json_decode($configContents, true);

$logo = $config["logo"];
$logoLink = $config["logo-link"];
$githubLogo = $config["github-logo"];
$githubLogoLink = $config["github-logo-link"];
$title =  $config["title"];
$vocabularyLink = "https://www.cidoc-crm.org/";
$vocabularyLabel = "the CIDOC CRM website";

// Using __FILE__ to reference the current file
$filename = __FILE__;
$lastModified = filemtime($filename);
$dateString = date("Y-m-d", $lastModified);

$invno = isset($_GET['invno']) ? htmlspecialchars($_GET['invno']) : '';

if (!$invno)
  {
ob_start();
  echo <<<END
    <div class="text-center mt-4">
      <h3>Welcome</h3>
      <p>Enter an invoice number to fetch RDF data or use one of the example queries below:</p>
      <a href="?invno=NG1" class="btn btn-secondary m-2">Example Query 1 (NG1)</a>
      <a href="?invno=NG1093" class="btn btn-secondary m-2">Example Query 2 (NG1093)</a>
      <a href="?invno=NG1234" class="btn btn-secondary m-2">Example Query 3 (NG1234)</a>
    </div>
END;

$body = ob_get_contents();
ob_end_clean(); // Don't send output to client
  }
else
  {
ob_start();
  echo <<<END
    <div class="row-titles">
                <div class="col-title h4 me-1">Original JSON Data</div>
                <div class="col-title h4 ms-1">Processed RDF Data</div>
            </div>
            <div class="row-content">
                <div class="col col-content me-1" id="jsonOutput">
		  <pre><code class="language-json" id="json-code"></code></pre>
                </div>
                <div class="col col-content ms-1"  id="rdfOutput">
                    <pre><code class="language-turtle" id="rdf-code"></code></pre>
                </div>
            </div>
END;

$body = ob_get_contents();
ob_end_clean(); // Don't send output to client
  }

echo <<<END

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>$title</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }
        .container-fluid {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .row-titles, .row-content {
            display: flex;
        }
        .row-content {
            flex: 1;
            overflow: hidden;
        }
        .col-title {
            flex: 1;
            text-align: center;
            padding: 10px;
            #background: #f8f9fa;
            #border: 1px solid #dee2e6;
            box-sizing: border-box;
        }
        .col-content {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #dee2e6;
        }
        footer {
            #background: #f8f9fa;
            padding: 10px;
            text-align: center;
        }
        th, td {
            text-align: left;
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg">
            <a class="navbar-brand" href="$logoLink">
                <img src="$logo" alt="Logo" style="height:38.391px;">
            </a>
            <div class="navbar-collapse collapse">
                <span style="margin:0px;" class="navbar-text h3">$title</span>
            </div>
            <form id="queryForm" class="d-flex ms-auto my-2 my-lg-0">
                <input class="form-control me-2" type="search"  id="invno" name="invno" placeholder="Enter Invoice Number" value="" aria-label="Search">
                <button class="btn btn-outline-success" onclick="fetchAndDisplayData()" type="submit">Search</button>
            </form>
        </nav>
        <div class="content">
            $body
        </div>
        <footer>
              <div class="row w-100">
       <div class="col d-flex justify-content-start">
      
      <div style="font-size:0.75rem; padding:0.5rem;">
	<p style="text-align: left;">Details of the CIDOC CRM ontology can be found at <a class="" style="color: black;" href="$vocabularyLink">$vocabularyLabel</a></p>
	<p style="text-align: left;">Last updated $dateString</p>
    </div>
      
      
      

      </div>
      <div class="col d-flex justify-content-end">
      <table class="table table-borderless" style="max-width:600px;">
  <tbody>
    <tr>
      <td style="font-size:0.75rem;">E-RIHS IP has received funding from the European Union’s Horizon Europe call HORIZON-INFRA-2021-DEV-02, Grant Agreement n.101079148. </td>
      <td></td>
      <td><a href="https://doi.org/10.3030/101079148" target="_blank"><img src="./graphics/eu-fundedby-logo-B6sKkV8V.png" alt="Funded by EU" class="object-contain object-left" style="height:1.75rem;"></a></td>
    </tr>
    <tr>
      <td style="font-size:0.75rem;">Developed by Joe Padfield / National Gallery (London) for E-RIHS</td>
      <td></td>
      <td><a href="https://www.nationalgallery.org.uk" target="_blank"><img src="./graphics/NG Logo.png" alt="The National Gallery" class="object-contain object-left" style="height:1.75rem;"></a></td>
    </tr>
    <tr>
      <td style="font-size:0.75rem;">Source code available on GitHub under the MIT License</td>
      <td></td>
      <td><a href="https://github.com/jpadfield/ngdata2crm" target="_blank"><img src="./graphics/github-mark.svg" alt="GitHub" class="object-contain object-left" style="height:1.75rem;"></a></td>
    </tr>
  </tbody>
</table>
      </div>
      </div>
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/json-viewer-js@latest/dist/json-viewer.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-turtle.min.js"></script>
    
    <script>
	function setCodeContent(element, content) {
    // Escape special characters
    // not used a t the momment
    var escapedContent = content.replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#039;');
    element.innerHTML = content;
  }
  
        function fetchAndDisplayData() {
            const invno = document.getElementById('invno').value;
            if (!invno) {
                alert("Please enter an invoice number.");
                return;
            }
            const url = `https://data.ng.ac.uk/es/public/_search?size=5&default_operator=AND&q=@datatype.base:object+identifier.value:${invno}&_source`;
            const shareableLink = `${window.location.origin}${window.location.pathname}?invno=${invno}`;
            history.pushState({}, '', shareableLink);

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    //document.getElementById('jsonOutput').innerHTML = JSON.stringify(data, null, 2);
		    document.getElementById("json-code").textContent = JSON.stringify(data, null, 2);
                    $.ajax({
                        url: 'data.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(data),
                        success: function(response) {
			//console.log(response)
                            //document.getElementById('rdfOutput').innerHTML = response;
                            //document.getElementById('outputPanels').style.display = 'flex';
			    
			    //document.getElementById("rdf-code").textContent = response;
			    var codeElement = document.getElementById("rdf-code");
			    setCodeContent(codeElement, response);
			    Prism.highlightAll();
                        },
                        error: function(error) {
                            console.error('Error:', error);
                        }
                    });
                })
                .catch(error => console.error('Fetch error:', error));
        }

        // Automatically fetch data if invno is provided in URL
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const invno = urlParams.get('invno');
            if (invno) {
                document.getElementById('invno').value = invno;
                fetchAndDisplayData();
            }
        });
    </script>
</body>
</html>


END;

/*
echo <<<END
  
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="./graphics/favicon.png">
  <title>$title</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <style>
    html, body {
      height: 100%;
    }
    .container-custom {
      max-width: 1200px;
      min-width: 800px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    .content {
      flex: 1;
      overflow-y: hidden;
            display: flex;
            flex-direction: column;
      border-top: 2px dotted #dee2e6;  
      border-bottom: 2px dotted #dee2e6;  
    }
    

        .row-content {
            flex: 1;
            display: flex;
            overflow: hidden;
        }
        .col-content {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            box-sizing: border-box;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            height: 100%;
        }
	
    .footer {
      display: flex;
      justify-content: space-between;
    }
  </style>
</head>
<body>
  
    <div class="container-fluid">
        <header class="py-3">
            <h1 class="text-center">Bootstrap Layout</h1>
        </header>
        <div class="content">
            <div class="row">
                <div class="col text-center"><strong>Column 1</strong></div>
                <div class="col text-center"><strong>Column 2</strong></div>
            </div>
            <div class="row row-content">
                <div class="col col-content">
                    <p>Content for column 1. This column will scroll if the content is too long.</p>
                    <!-- Add more content here to test scrolling -->
                    <p>More content...</p>
                    <p>More content...</p>
                    <p>More content...</p>
                    <p>More content...</p>
                    <p>More content...</p>
                    <p>More content...</p>
                    <p>More content...</p>
                </div>
                <div class="col col-content">
                    <p>Content for column 2. This column will scroll if the content is too long.</p>
                    <!-- Add more content here to test scrolling -->
                    <p>More content...</p>
                    <p>More content...</p>
                    <p>More content...</p>
                    <p>More content...</p>
                    <p>More content...</p>
                    <p>More content...</p>
                    <p>More content...</p>
                </div>
            </div>
        </div>
        <footer>
            <p>Footer Content</p>
        </footer>
    </div>
  

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
   function fetchAndDisplayData() {
            const invno = document.getElementById('invno').value;
            if (!invno) {
                alert("Please enter an invoice number.");
                return;
            }
            const url = `https://data.ng.ac.uk/es/public/_search?size=5&default_operator=AND&q=@datatype.base:object+identifier.value:${invno}&_source`;
            const shareableLink = `${window.location.origin}${window.location.pathname}?invno=${invno}`;
            history.pushState({}, '', shareableLink);

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('jsonOutput').innerHTML = JSON.stringify(data, null, 2);
                    $.ajax({
                        url: 'data.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(data),
                        success: function(response) {
                            document.getElementById('rdfOutput').innerHTML = response;
                            document.getElementById('outputPanels').style.display = 'flex';
                        },
                        error: function(error) {
                            console.error('Error:', error);
                        }
                    });
                })
                .catch(error => console.error('Fetch error:', error));
        }

        // Automatically fetch data if invno is provided in URL
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const invno = urlParams.get('invno');
            if (invno) {
                document.getElementById('invno').value = invno;
                fetchAndDisplayData();
            }
        });

    
  </script>
</body>
</html>

END;
//*/






/*
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>RDF Query Interface</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js" integrity="sha512-ykZ1QQr0Jy/4ZkvKuqWn4iF3lqPZyij9iRv6sGqLRdTPkY69YX6+7wvVGmsdBbiIfN/8OdsI7HABjvEok6ZopQ==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.35.0/ace.js" integrity="sha512-VBSPPzA3vXv9TFRJtm9ZR4YAJM59ihY4K8+b4LtJenbKk7OfwbAR0v40IJICmfSA6t3caE49CYz9q7mkk/2YPg==" crossorigin="anonymous"></script>
    <style>
        body, html {
            height: 100%;
        }
        .container {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        #outputPanels {
            flex: 1;
            display: flex;
            flex-direction: row;
            gap: 20px;
            overflow: auto;
        }
        .panel {
            flex: 1;
            overflow: auto;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            height: 100%;
        }
        footer {
            background: #f8f9fa;
            padding: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row mt-4">
            <div class="col-md-12">
                <h1 class="text-center">RDF Query Interface</h1>
                <form id="queryForm" class="form-inline justify-content-center mt-4">
                    <div class="form-group mx-sm-3 mb-2">
                        <label for="invno" class="sr-only">Invoice Number</label>
                        <input type="text" class="form-control" id="invno" name="invno" placeholder="Enter Invoice Number" value="<?php echo isset($_GET['invno']) ? htmlspecialchars($_GET['invno']) : ''; ?>">
                    </div>
                    <button type="button" class="btn btn-primary mb-2" onclick="fetchAndDisplayData()">Submit</button>
                </form>
                <?php if (!isset($_GET['invno'])): ?>
                    <div class="text-center mt-4">
                        <h3>Welcome</h3>
                        <p>Enter an invoice number to fetch RDF data or use one of the example queries below:</p>
                        <a href="index.php?invno=NG1" class="btn btn-secondary m-2">Example Query 1 (NG1)</a>
                        <a href="index.php?invno=NG1093" class="btn btn-secondary m-2">Example Query 2 (NG1093)</a>
                        <a href="index.php?invno=NG1234" class="btn btn-secondary m-2">Example Query 3 (NG1234)</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="row mt-4 content">
            <div class="col-md-12">
                <div id="outputPanels" style="display: none;">
                    <div class="panel">
                        <h3>Original JSON Data</h3>
                        <pre id="jsonOutput"></pre>
                    </div>
                    <div class="panel">
                        <h3>Processed RDF Data</h3>
                        <pre id="rdfOutput"></pre>
                    </div>
                </div>
            </div>
        </div>
        <footer>
            <p>Content to follow</p>
        </footer>
    </div>

    <script>
        function fetchAndDisplayData() {
            const invno = document.getElementById('invno').value;
            if (!invno) {
                alert("Please enter an invoice number.");
                return;
            }
            const url = `https://data.ng.ac.uk/es/public/_search?size=5&default_operator=AND&q=@datatype.base:object+identifier.value:${invno}&_source`;
            const shareableLink = `${window.location.origin}${window.location.pathname}?invno=${invno}`;
            history.pushState({}, '', shareableLink);

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('jsonOutput').innerHTML = JSON.stringify(data, null, 2);
                    $.ajax({
                        url: 'data.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(data),
                        success: function(response) {
                            document.getElementById('rdfOutput').innerHTML = response;
                            document.getElementById('outputPanels').style.display = 'flex';
                        },
                        error: function(error) {
                            console.error('Error:', error);
                        }
                    });
                })
                .catch(error => console.error('Fetch error:', error));
        }

        // Automatically fetch data if invno is provided in URL
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const invno = urlParams.get('invno');
            if (invno) {
                document.getElementById('invno').value = invno;
                fetchAndDisplayData();
            }
        });
    </script>
</body>
</html>*/

?>
