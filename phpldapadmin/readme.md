__臺北市教育人員目錄服務 管理後台__，為臺北市政府教育局客製化之 openldap 伺服器管理後台，在知名的 phpldapadmin 套件中新增各種臺北市自訂 schema 的模板，請參考 [templates](https://github.com/leejoneshane/tpeduSSO/tree/master/phpldapadmin/templates) 資料夾， environment 資料夾之設定為測試環境參數，若要用於正式對外服務請務必修改。

這個 Docker 映像檔，使用 [osixia/phpldapadmin](https://github.com/osixia/docker-phpLDAPadmin) 做為基礎映像檔。
相關技術文件資料請前往該專案的 Github 頁面查閱。

本系統由臺北市老師自主研發，若您為臺北市教師且對程式開發感興趣，請與我聯繫！

## 啟動容器
```
docker run -p 8080:80 -d leejoneshane/tpedusso:phpldapadmin
```

連結到 http://localhost:8080 ，請使用 openldap 伺服器的測試帳號登入，帳號為 cn=admin,dc=tp,dc=edu,dc=tw，密碼為 test。

您可以把這個執行中的容器，當成 [臺北市教育人員單一身份驗證服務平台](https://ldap.tp.edu.tw) 的目錄管理後台，以便於開發程式的過程中，進行各種測試。

## 建置映像檔

如果您有自行建置伺服器的需求，請先安裝 docker 在您的伺服器上，並修改 Dockerfile 文件，然後再執行底下指令：
```
docker build .
```

本專案所有程式碼開源，歡迎各縣市網路中心自行下載使用。
