import { useEffect, useState, useContext } from "react";
import Connection from "services/Connection";
import UserCtx from "state/user-context";
import './mail_list_styles.css';

const con = new Connection();

export default function MailList() {
  const userCtx = useContext(UserCtx);
  const [mailList, setMailList] = useState([]);

  useEffect(() => {
    //Get mail listP
    con.userGet("user/maillist/guest/get", userCtx.userToken).then((result) => {
      if (!result.data) {
        return;
      }
      setMailList(result.data);
    });
  }, []);

  return (
    <div class="mail_list_wrapper">
      {mailList.map((x) => x.email).join(", ")}
    </div>
  );
}
