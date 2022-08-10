<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# Blockchain network设置相关参数
################################################

$GLOBALS['deploy']['defaultNetwork'] = 'flow'; //默认网络

$GLOBALS['deploy']['network']['flow'] = array(); //对flow的支持
    $GLOBALS['deploy']['network']['flow']['serviceSalt'] = 'TestSalt2022'; //Salt for communicating with service
    
    $GLOBALS['deploy']['network']['flow']['class'] = array( //对这个network定制的类
        'wallet' => 'user\\wallet\\xFlow', //钱包
        'addressValidator' => 'validator\\flow', //地址验证器
        'transactor' => 'transactor\\flow', //交易处理器
    );

    $GLOBALS['deploy']['network']['flow']['url'] = array( //对应这个network的地址配置
        'manageWallet' => 'https://id.ecdao.org/me', //钱包管理工具
        'deposit' => 'https://renas.vercel.app/deposit?userId={?$uid?}' //充值地址
    );

    $GLOBALS['deploy']['network']['flow']['browser'] = array( //区块链浏览器配置
        'account' => 'https://flowscan.org/account/{?--walletAddress?}', //地址查看
        'transaction' => 'https://flowscan.org/transaction/{?--txId?}' //交易查询
    );

    $GLOBALS['deploy']['network']['flow']['API'] = array( //这个network的API配置
        'withdraw' => 'https://renas.vercel.app/api/auth/withdraw', //提现
        'transactionResult' => 'https://rest-mainnet.onflow.org/v1/transaction_results/' //交易结果查询
    );

    $GLOBALS['deploy']['network']['flow']['asset'] = array( //在这条链上的资产合约映射表
        'cp' => 'A.98c9c2e548b84d31.ContributionPoint', //cp token smart contract address
        'cp.identifier' => 'contributionPointVault', //cp token identifier
        'cp.recieverPath' => 'contributionPointReciever' //cp token reciever vault
    );
?>