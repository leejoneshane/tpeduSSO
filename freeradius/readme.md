__臺北市教育人員無線認證服務__，為臺北市政府教育局所發展教育人員單一身份驗證的高效率 freeradius 伺服器，大小僅 7 MB，由臺北市老師自主研發，若您為臺北市教師且對程式開發感興趣，請與我聯繫！

這個資料夾內的資料是用來建置一台 freeradius server 的 Docker 映像檔，伺服器設定成透過 openldap 伺服器驗證使用者是否為教育人員，如果找不到該帳號，則會將封包轉送到臺北市教育網路中心的 __臺灣學術網路無線漫遊伺服器__ 上進行漫遊，詳情可查閱 [default](https://github.com/leejoneshane/tpeduSSO/tree/master/freeradius/default) 文件。

上述設定可以透過 docker 環境變數調整，以適應各縣市的需求，請參考下方關於執行環境的說明。

## 啟動容器
```
docker run -p 1812:1812/udp -p 1813:1813/udp -d leejoneshane/tpedusso:freeradius
```

您可以使用底下指令測試伺服器是否正常運行，如果您看到伺服器回應 Request-Accept，即代表伺服器正常運行中。
```
radtest meps123456789 test localhost 1812 testing123
```

您可以把這個執行中的容器，當成 [臺北市教育人員單一身份驗證服務平台](https://ldap.tp.edu.tw) 無線認證服務，以便於開發教育人員單一身份驗證的各項應用服務。

## 執行環境

若要讓系統實際上線服務，請務必在 docker run 指令中使用 -e 附加環境變數，說明如下：

* __LDAP_HOST: ldap://172.22.0.4__ 不可省略，openldap 伺服器的 URL，若使用容器快取伺服器，請把 IP 改為容器名稱。
* __LDAP_ROOTDN: cn=admin,dc=tp,dc=edu,dc=tw__ 不可省略，openldap 伺服器管理員的 DN。
* __LDAP_ROOTPWD: test__ 不可省略，openldap 伺服器的管理員網路連線密碼。
* __LDAP_BASEDN: ou=account,dc=tp,dc=edu,dc=tw__ 不可省略，為後端 openldap 伺服器用來儲存帳號資訊的子目錄。
* __SECRET: tpeduaaa__ 不可省略，為本伺服器的預設連線密碼。
* __PROXY_TO_HOST: 163.21.249.130__ 不可省略，為各縣市無線漫遊伺服器之IP。
* __PROXY_SECRET: 587__ 不可省略，為各縣市無線漫遊伺服器之連線密碼。

## 建置映像檔

如果您有自行建置伺服器的需求，請先安裝 docker 在您的伺服器上，並修改 Dockerfile 文件，然後再執行底下指令：
```
docker build .
```

本專案所有程式碼開源，歡迎各縣市網路中心自行下載使用。
