var Sup = [1,2,3,5,7,8,11,13,16,18,19,20,21,23,24,25,28,29,31,34,35,36,37,40,45,46,48];
Sup = Sup.concat(Sup.map(function(x){return x+49;}));
var Uns = [0,4,6,9,10,12,14,15,17,22,26,27,30,32,33,38,39,41,42,43,44,47];
Uns = Uns.concat(Uns.map(function(x){return x+49;}));


var Ans = [0,0,0,0,0,0,0,0,1,2,3,4,5,6,0,2,4,6,1,3,5,0,3,6,2,5,1,4,0,4,1,5,2,6,3,0,5,3,1,6,4,2,0,6,5,4,3,2,1,null,0,0,0,0,0,0,null,1,4,5,2,3,6,null,2,1,3,4,6,5,null,3,5,1,6,2,4,null,4,2,6,1,5,3,null,5,6,4,3,1,2,null,6,3,2,5,4,1];

// Get idxs of all null problems
var BadIdx = Ans.reduce(function(a,e,i) {if (e === null) {a.push(i);} return a;}, []);

for (var ii = 0; ii < BadIdx.length; ii++) {
    for (var jj = 0; jj < Sup.length; jj++) {
        if (BadIdx[ii] === Sup[jj]){
            Sup.splice(jj,1);
        }
    }
    for (var jj = 0; jj < Uns.length; jj++) {
        if (BadIdx[ii] === Uns[jj]){
            Uns.splice(jj,1);
        }
    }
}

var Sym = ['./Imgs/S0.png', './Imgs/S1.png'];