import sys
import datetime
import json
import sql_appbk
import time

#生成code
TOKEN_TEMPLATE = "" #token的模板
NFT_TEMPLATE = "" #NFT的模板
BOX_TEMPLATE = "" #盲盒的模板

def load_template():
    global TOKEN_TEMPLATE
    TOKEN_TEMPLATE = open("ExampleToken.cdc","r").read()
    #print(TOKEN_TEMPLATE)

    global NFT_TEMPLATE
    NFT_TEMPLATE = open("ExampleNFT.cdc", "r").read()

    global BOX_TEMPLATE
    BOX_TEMPLATE = open("LucycanvasNFT.cdc", "r").read()

    global BOX_TEMPLATE_VUE
    BOX_TEMPLATE_VUE = open("lucky_box.vue", "r").read()

#加载模板
load_template()


#产生token合约
def generate_token_contract(name="Wow"):
    global TOKEN_TEMPLATE
    ori_name = "Example"
    token_contract = TOKEN_TEMPLATE.replace(ori_name, name)
    #print(token_contract)
    return token_contract


#产生NFT合约
def generate_nft_contract(name="Wow"):
    global NFT_TEMPLATE
    ori_name = "Example"
    nft_contract = NFT_TEMPLATE.replace(ori_name, name)
    #print(nft_contract)
    return nft_contract


#产生NFT合约
#name，合约名称
#let quality_prob，物品概率，如 {"纸巾":0.5, "鼠标":0.3, "键盘":0.14,"iPad":0.05,"Macbook":0.01}
def generate_box_contract(name="Wow", quality_prob={}):
    global BOX_TEMPLATE
    ori_name = "Example"
    nft_contract = BOX_TEMPLATE.replace(ori_name, name)
    #替换概率
    nft_contract = nft_contract.replace("ITEM_PROB_DICT", json.dumps(quality_prob, ensure_ascii=False))
    #print(nft_contract)
    return nft_contract

"""
输入：name， 合约名
输入：contract_address，合约地址
输入：quality_prob，物品概率，如 {"纸巾":0.5, "鼠标":0.3, "键盘":0.14,"iPad":0.05,"Macbook":0.01}
返回：vue代码
"""
def generate_box_vue(name="Wow", contract_address="0x01", quality_prob={}):
    print(quality_prob)
    global BOX_TEMPLATE_VUE #vue合约字符串
    #step 1,替换合约名称
    vue_code = BOX_TEMPLATE_VUE.replace('{CONTRACT_NAME}', name)

    #step 2,替换合约地址
    vue_code = vue_code.replace('{CONTRACT_ADDRESS}', contract_address)

    #step 3,{ITEM_PROB_LIST},物品概率
    #先构造
    item_prob_list = []
    for key in quality_prob:
        prob = quality_prob[key]
        data = {
                "name": key,
                "prob": prob
            }
        item_prob_list.append(data)

    vue_code = vue_code.replace('{ITEM_PROB_LIST}', json.dumps(item_prob_list, ensure_ascii=False))

    #step4, 处理{PRIZES_LIST}
    prizes_list = []
    i = 0
    for key in quality_prob:
        if 0==i%2:
            color = '#e9e8fe'
        else:
            color = '#b8c5f2'

        data = {
                "background": color,
                "fonts": [{"text": key, "top": '10%'}]
            }
        prizes_list.append(data)
        i = i + 1

    vue_code = vue_code.replace('{PRIZES_LIST}', json.dumps(prizes_list, ensure_ascii=False))

    #step 5, 处理 {PRIZE_INDEX}
    vue_code = vue_code.replace('{PRIZE_INDEX}', json.dumps(quality_prob, ensure_ascii=False))

    return vue_code



if __name__=="__main__":
    name = "WowWar"
    #generate_token_contract(name)
    #generate_nft_contract(name)

    quality_prob =  {"纸巾":0.5, "鼠标":0.3, "键盘":0.14,"iPad":0.05,"Macbook":0.01}
    #code = generate_box_contract(name, quality_prob)
    #print(code)
    contract_address = "0x01"
    code = generate_box_vue(name, contract_address, quality_prob)
    print(code)
