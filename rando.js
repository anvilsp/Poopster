async function generateArray(url) {
    try {
        const response = await fetch(url); // fetch from provided url
        const text = await response.text();
        const json = await JSON.parse(text); // parse text
        return json;
    } catch (err) {
        console.error(err);
    }
}

window.onload = function(){

    generateArray("poops.php?parse=web").then(result => {
        document.getElementById("oopsWorld").innerHTML = result.world;
        document.getElementById("oopsResult").innerHTML = result.stagename;
        document.getElementById("oopsOriginal").innerHTML = result.first_stage + " (" + result.first_context + ") and "
        + result.second_stage + " (" + result.second_context + ")";
        document.getElementById("oopsSeed").innerHTML = "Seed: " + result.seed;
    });

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

    document.getElementById("oopsResult").onclick = function() { // clicking original names toggles
        var originalNames = document.getElementById("oopsOriginal");
        var oopsSeed = document.getElementById("oopsSeed");
        if (originalNames.style.visibility == "hidden") {
            originalNames.style.visibility = "visible";
            oopsSeed.style.visibility = "visible";
        } else {
            originalNames.style.visibility = "hidden";
            oopsSeed.style.visibility = "hidden";
        }
    }

}

function handleForm(event) {
    event.preventDefault();

    let value = document.getElementById('seed').value;
    let url = `poops.php?parse=web&input=${encodeURIComponent(value)}`;

    generateArray(url).then(result => {
        if(value != "") {
            document.getElementById("seed").placeholder = value
        }
        else
        {
            document.getElementById("seed").placeholder = "Seed (Optional)"
        }
        document.getElementById("seed").value = null
        document.getElementById("oopsWorld").innerHTML = result.world;
        document.getElementById("oopsResult").innerHTML = result.stagename;
        document.getElementById("oopsOriginal").innerHTML = result.first_stage + " (" + result.first_context + ") and "
        + result.second_stage + " (" + result.second_context + ")";
        let seed_str = "";
        let flag_og = result.flags.toString();
        for(let flag of flag_og.split(',')) {
            seed_str = seed_str + flag + " ";
        }
        document.getElementById("oopsSeed").innerHTML = "Seed: " + seed_str + result.seed;
        
        // hide original names if they're currently visible
        var originalNames = document.getElementById("oopsOriginal");
        var oopsSeed = document.getElementById("oopsSeed");
        if (originalNames.style.visibility == "visible") {
            originalNames.style.visibility = "hidden";
            oopsSeed.style.visibility = "hidden";
        }
    })
}