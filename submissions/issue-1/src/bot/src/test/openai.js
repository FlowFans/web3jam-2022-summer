const openai = require('../libs/openai')

const fetch = async () => {
	const response = await openai.createCompletion({
		model: 'text-davinci-002',
		prompt: 'What is the meaning of life?',
		temperature: 0.9,
		top_p: 1,
		max_tokens: 100,
		frequency_penalty: 0,
		presence_penalty: 0.6,
		n: 1,
		best_of: 1,
		suffix: null,
		echo: true,
		stream: true,
	}, {
		responseType: "stream"
	})
	const stream = response.data
	let anser = ''
	stream.on('data', data => {
		const message = data.toString()
		try {
			const token = JSON.parse(message.slice(6)).choices[0].text
			anser += token
			console.log(anser)
		} catch (_) {
		}
	});
	
	stream.on('end', () => {
		console.log("stream done");
	});
}

fetch()