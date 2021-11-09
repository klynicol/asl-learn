import React, { useState, useRef } from "react";
//https://dev.to/alexandprivate/your-next-react-modal-with-your-own-usemodal-hook-context-api-3jg7

export default function useModal() {
  const [modal, setModal] = React.useState(false);
  const [modalContent, setModalContent] = React.useState(
    "I'm the Modal Content"
  );
  const [modalTitle, setModalTitle] = useState(null);
  const [isConfirm, setIsConfirm] = useState(false);

  const confirmCallback = useRef(()=> {});

  let handleModal = (inputObj = {}) => {
    setModal(!modal);
    if (inputObj.content) {
      setModalContent(inputObj.content);
    }
    if (inputObj.title) {
      setModalTitle(inputObj.title);
    }
    if (inputObj.confirmCallback) {
      setIsConfirm(true);
      confirmCallback.current = inputObj.confirmCallback;
    }
  };

  return {
    modal,
    handleModal,
    modalContent,
    modalTitle,
    isConfirm,
    confirmCallback,
  };
}
