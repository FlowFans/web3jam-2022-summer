const { Snowflake } = require('nodejs-snowflake');

const uid = new Snowflake({
	custom_epoch: 1656604800000,
	instance_id: 0,
});

module.exports = uid