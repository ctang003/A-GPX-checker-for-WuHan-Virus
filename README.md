# A-GPX-checker-for-WuHan-Virus
This is a simple php code that takes two GPX files, slices them into a uniform time resolution, and calculates the distance between two track points with the same time tag.   

實作測試 http://104.199.142.194/index.php

沒時間來改善，只做了一些基本功能，目前可以檢查兩筆 GPX 在同一個時間點的距離，所以假如有確診病患的 GPX 就可以比對了，需要的就拿去改，我也只是順手寫一下而已
這程式不留資料，也不顯示地圖，個資的疑慮應該可以降到最低
網路上說用 Google Time Line 是可以，只是那個轉出來的 GPX 可能有點粗糙，個人建議是用 strava，馬拉松世界，或任何 GPS 可以得到 TCX 或 GPX 的 App 都可以
TCX 轉 GPX 可以用 GPSBabel 或到 https://www.gpsvisualizer.com/gpsbabel/ 去轉
我不喜歡秀地圖，怕會影響熱區附近的商家
