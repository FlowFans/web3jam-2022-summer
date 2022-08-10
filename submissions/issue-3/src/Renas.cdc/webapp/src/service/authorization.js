import * as fcl from "@onflow/fcl"

export const getAuth = (code, address) => async (account = {}) => {
    const addr = '98c9c2e548b84d31';
    const keyId = 2;

    const { account: user } = await fcl.send([fcl.getAccount(addr)]);

    const key = user.keys[keyId];
    let sequenceNum;
    if (account.role.proposer) sequenceNum = key.sequenceNumber;

    const signingFunction = async data => {
        const signature = await fetch(
            `https://flow-wallet-testnet.blocto.app/api/flow/caa-sign`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    address,
                    code,
                    data,
                })
            }
        )
            .then(response => response.json())
            .then(response => response.signature)

        return ({
            addr,
            keyId: key.index,
            signature,
        });
    };

    return {
        ...account,
        addr,
        keyId: key.index,
        sequenceNum,
        signature: account.signature || null,
        signingFunction,
        resolve: null,
        roles: account.roles,
    };
};
