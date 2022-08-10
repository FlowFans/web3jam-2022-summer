# web3jam-project - LearntVerse

# node
Ensure you have NodeJS installed. Version 16+ is required.
```
node -v
> v16.5.0
```

# flow-cli
```
# Install flow-cli with the latest version
sh -ci "$(curl -fsSL https://storage.googleapis.com/flow-cli/install.sh)"

# Check flow-cli
flow version
> Version: v0.38.0
> Commit: 0095fb2a585baa03b06b9fa086451d797857d355
```

# Prepare
```
cd src/ui

npm install @onflow/fcl --save

npm run dev
```

# Deploy contracts
Two Ways to deploy contracts, Please choose one of them:
1. FCL : `flow project deploy --network=testnet --update`
2. https://flow-view-source.com/ is support to deploy contracts for Wallect account

# Run
Opne `http://localhost:3000` on browser.
