import React, { useState, useEffect } from 'react';
import * as fcl from '@onflow/fcl';

export const useAuthSession = () => {
  const [user, setUser] = useState(null);
  useEffect(() => fcl.currentUser.subscribe(setUser), []);

  return user;
};
