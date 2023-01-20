async function generateArray(url) {
    try {
        const response = await fetch(url); // fetch from provided url
        const text = await response.text(); // parse text
        let array = text.split('\n'); // populate array with each word, separated by newlines
        return array;
    } catch (err) {
        console.error(err);
    }
}

function randomRange(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
}

let monkeySourceOne = 'https://anvilsp.github.io/stagename/smbdx/smbdx-firsthalf.txt'
let monkeySourceTwo = 'https://anvilsp.github.io/stagename/smbdx/smbdx-secondhalf.txt'
let ratSourceOne = 'https://anvilsp.github.io/stagename/rolledout/ro-firsthalf.txt'
let ratSourceTwo = 'https://anvilsp.github.io/stagename/rolledout/ro-secondhalf.txt'
let monkeyFull = 'https://anvilsp.github.io/stagename/smbdx/smbdx-stagename.txt'
let ratFull = 'https://anvilsp.github.io/stagename/rolledout/ro-stagename.txt'

let stageHalf1 // first random half
let stageHalf2 // second random half
let fullStage1 // first full stage name
let fullStage2 // second full stage name

// determine if the stage is a rolled out stage (1) or a monkey ball stage (0)
let rng1
let rng2

async function generateOopster() { // do all of the randomization stuff. javascript scares me
    let array1;
    let array2;
    let fullArray1;
    let fullArray2;
    rng1 = randomRange(0, 1);
    rng2 = randomRange(0, 1);

    if(rng1 == 0) { // populate the first half with monkey ball levels
        array1 = await generateArray(monkeySourceOne);
        fullArray1 = await generateArray(monkeyFull);
    }
    else { // populate the first half with rolled out levels
        array1 = await generateArray(ratSourceOne);
        fullArray1 = await generateArray(ratFull);
    }

    if(rng2 == 0) { // populate the second half with monkey ball levels
        array2 = await generateArray(monkeySourceTwo);
        fullArray2 = await generateArray(monkeyFull);
    }
    else { // populate the second half with rolled out levels
        array2 = await generateArray(ratSourceTwo);
        fullArray2 = await generateArray(ratFull);
    }

    let generateRng1 = randomRange(0, array1.length - 1) // determine random first half
    let generateRng2 = randomRange(0, array2.length - 1) // determine random second half
    // set stage half variables
    stageHalf1 = array1[generateRng1]
    stageHalf2 = array2[generateRng2]
    // set full stage variables
    fullStage1 = fullArray1[generateRng1].replace(/_/g, ' ');
    fullStage2 = fullArray2[generateRng2].replace(/_/g, ' ');
    // concatenate halves
    let oopster = stageHalf1.concat(stageHalf2).replace(/_/g, ' ');
    return oopster
}

window.onload = function(){

    generateOopster().then(result => {
        document.getElementById("oopsResult").innerHTML = result;
        document.getElementById("oopsOriginal").innerHTML =  fullStage1.concat(" and ".concat(fullStage2));
    });
    
    document.getElementById("makeOopster").onclick = function(){ // click generate button to generate the oopsie
        generateOopster().then(result => {
            document.getElementById("oopsResult").innerHTML = result;
            document.getElementById("oopsOriginal").innerHTML =  fullStage1.concat(" and ".concat(fullStage2));
        });
        
        // hide original names if they're currently visible
        var originalNames = document.getElementById("oopsOriginal");
        if (originalNames.style.visibility == "visible") {
            originalNames.style.visibility = "hidden";
        }
    }

    document.getElementById("oopsResult").onclick = function() { // clicking original names toggles
        var originalNames = document.getElementById("oopsOriginal");
        if (originalNames.style.visibility == "hidden") {
            originalNames.style.visibility = "visible";
        } else {
            originalNames.style.visibility = "hidden";
        }
    }
}