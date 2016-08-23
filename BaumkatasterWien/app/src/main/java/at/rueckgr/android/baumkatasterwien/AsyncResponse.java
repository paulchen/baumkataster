package at.rueckgr.android.baumkatasterwien;

import java.util.List;

/**
 * Created by paulchen on 17.07.16.
 */
public interface AsyncResponse {
    void onSuccess(List<Tree> trees);
}
