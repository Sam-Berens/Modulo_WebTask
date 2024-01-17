// S00: Obtain one 64-bit unsigned integer to use as a seed...
// ... and then break the seed up into two unsigned integers (mW and mZ);
var mW = NaN;
var mZ = NaN;
var RngSeeded = false;
async function  SeedRng(){
	var p = await fetch('./RandomTools/GetRngSeed.php');
	var seed = await p.json();
	
	// Break the seed into two 32-bit unsigned integers (mW and mZ);
	var seedStr = seed.toString();
	var seedStrLen = seed.toString().length;
	var mWStrLen = Math.floor(seedStrLen/2);
	var mZStrLen = seedStrLen-mWStrLen;
	mW = parseInt(seedStr.substr(0,mWStrLen),10);
	mZ = parseInt(seedStr.substr(mWStrLen,mZStrLen),10);
	
	// Set the value of RngSeeded to be true;
	RngSeeded = true;
}

// S01: Specify Rng()...
// ... this generates a uniformly distributed random number in the [0,1] interval;
// The optional input argument specifies how many random numbers to generate (default =1);
// If more than one random number is requested, the function returns an Array of Numbers;
// If only one random number is requested, the function returns a Number;
function Rng(n = 1) {
	var r = Array(n);
	for (ii = 0; ii < n; ii++) {
		mW = (18000 * (mW & 65535) + (mW >> 16)) & 0xffffffff;
		mZ = (36969 * (mZ & 65535) + (mZ >> 16)) & 0xffffffff;
		var cr = ((mZ << 16) + mW) & 0xffffffff;
		cr /= 4294967296;
		r[ii] = cr + 0.5;
	}
	if (n==1) {
		return r[0];
	} else {
		return r;
	}
}