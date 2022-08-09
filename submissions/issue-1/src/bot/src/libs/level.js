function parseExpToLevel(exp) {
	let level = 0;
	while (Math.E ** level + Math.E * level + Math.E <= exp) {
		level++;
	}
	return level;
}

function parseLevelToExp(level) {
	return level ** Math.E + level * Math.E + level;
}

function parseLevelToSymbol(level) {
	const _level = level.toString(4);
	let symbol = '';
	if (_level.length >= 5) {
		symbol = '🔥🔥🔥🔥';
		return symbol
	}
	for (let i = 0; i < _level.length; i++) {
		for (let j = 0; j < _level[i]; j++) {
			switch (_level.length - i) {
			case 4:
				symbol += '🔥';
				break;
			case 3:
				symbol += '🌞';
				break;
			case 2:
				symbol += '🌛';
				break;
			case 1:
				symbol += '⭐️';
				break;
			default:
				break;
			}
		}
	}
	return symbol
}


module.exports = {
	parseExpToLevel,
	parseLevelToSymbol,
}

for (let i = 0; i <= 256; i++) {
	console.log(i, parseLevelToExp(i))
}

