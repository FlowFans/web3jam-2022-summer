import useSWR from 'swr';
import { useAuthSession } from './useAuthSession';
import { SoulMadeService } from '../services/SoulMadeService';

export const useUserCapability = () => {
  const user = useAuthSession();
  const service = SoulMadeService.getInstance();

  const { data, error } = useSWR(
    () => `GET_CAPABILITY_${user.addr}`,
    async () => {
      return await service.getCapability(user.addr);
    },
  );

  return { data, error, loading: !data && !error };
};
