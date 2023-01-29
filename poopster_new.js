async function generateArray(url) {
    try {
        const response = await fetch(url); // fetch from provided url
        const text = await response.text(); // parse text
        let array = text.split(';'); // separate by comma
        return array;
    } catch (err) {
        console.error(err);
    }
}

window.onload = function(){

    generateArray("poops.php?input=context").then(result => {
        document.getElementById("oopsResult").innerHTML = result[0];
        document.getElementById("oopsOriginal").innerHTML =  result[1].replace(/[\[\]']+/g, '');
    });

    document.getElementById("makeOopster").onclick = function(){ // click generate button to generate the oopsie
        generateArray("poops.php?input=context").then(result => {
            document.getElementById("oopsResult").innerHTML = result[0];
            document.getElementById("oopsOriginal").innerHTML =  result[1].replace(/[\[\]']+/g, '');
            
            // hide original names if they're currently visible
            var originalNames = document.getElementById("oopsOriginal");
            if (originalNames.style.visibility == "visible") {
                originalNames.style.visibility = "hidden";
            }
        })
    }

    document.getElementById("oopsResult").onclick = function() { // clicking original names toggles
        var originalNames = document.getElementById("oopsOriginal");
        if (originalNames.style.visibility == "hidden") {
            originalNames.style.visibility = "visible";
        } else {
            originalNames.style.visibility = "hidden";
        }
    }

    document.getElementById("api").onclick = function() {
        var apiBox = document.getElementById("api_box")
        if (apiBox.style.display == "none")
            apiBox.style.display = "block";
        else
            apiBox.style.display = "none";
    }

    document.getElementById("api_box").onclick = function() {
        this.setSelectionRange(0, this.value.length);
    }
}