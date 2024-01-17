// Function to shuffle any array;
function Shuffle(array) {
	
	// Check that Rng() has already been seeded and throw an error if not
	if (!RngSeeded) {
		throw "Error: Rng() has not been seeded!";
	}
	
	// Initialise some variables;
    let currentIndex = array.length,  randomIndex;
    
    // While there remain elements to shuffle.
    while (currentIndex != 0) {
        // Pick a remaining element.
        randomIndex = Math.floor(Rng() * currentIndex);
        currentIndex--;
        
        // And swap it with the current element.
        [array[currentIndex], array[randomIndex]] = [array[randomIndex], array[currentIndex]];
    }
    return array;
}