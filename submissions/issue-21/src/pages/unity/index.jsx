import React from "react";
import Unity, { UnityContent } from "react-unity-webgl";


  const  unityContent = new UnityContent(
    "Build/test.json",
    "Build/UnityLoader.js"
  );

function App() {
  // 一定要给Unity组件设置width和height属性，否则Canvas将无限增大最终导致浏览器卡死
  return <Unity style={{'width': '100%', 'height': '100%'}} unityContent={unityContent}/>;
}

export default App;

