@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $post->title }}</h1>
        {{ $post->updated_at->toFormattedDateString() }}
        @if ($post->published)
            <span class="label label-success" style="margin-left:15px;">Published</span>
        @else
            <span class="label label-default" style="margin-left:15px;">Draft</span>
        @endif
        <hr/>
        <p class="lead">
            {{ $post->content }}
        </p>
        <hr/>

        <h3>Comments:</h3>
        <div style="margin-bottom:50px;" v-if="user">
            <textarea class="form-control" rows="3" name="body" cols="" placeholder="Leave a comment"
                      v-model="commentBox"></textarea>
            <button class="btn btn-success" style="margin-top:10px" @click.prevent="storeComment">Save Comment</button>
        </div>
        <div v-else>
            <h4>You must be logged in to submit a comment!</h4> <a href="/login">Login Now &gt;&gt;</a>
        </div>


        <div class="media" style="margin-top:20px;" v-for="comment in comments">
            <div class="media-left">
                <a href="#">
                    <img class="media-object" src="http://placeimg.com/80/80" alt="...">
                </a>
            </div>
            <div class="media-body">
                <h4 class="media-heading" style="margin-left: 10px">@{{ comment.user.name }} said</h4>
                <p style="padding-left: 10px">@{{ comment.body }}</p>
                <span style="color: #aaa;">on @{{ comment.created_at }}</span>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script type="text/javascript">
        const app = new Vue({
            el: "#app",
            data: {
                comments: {},
                commentBox: '',
                post:{!! $post->toJson() !!},
                user:{!! auth()->check() ? auth()->user()->toJson() : 'null' !!}
            },
            mounted() {
                this.getComments();
                this.listen();
            },
            methods: {
                getComments() {
                    axios.get(`/api/posts/${this.post.id}/comments`).then((response) => {
                        this.comments = response.data;
                    }).catch((err) => {
                        console.log("we have error: ");
                        console.log(err);
                    })
                },
                storeComment() {
                    axios.post(`/api/posts/${this.post.id}/comment`, {
                        api_token: this.user.api_token,
                        body: this.commentBox
                    }).then((response) => {
                        this.comments.unshift(response.data);
                        this.commentBox = '';
                        //console.log(response);
                    }).catch((err) => {
                        console.log("we have error: ");
                        console.log(err);
                    })
                },
                listen() {
                    window.Echo.private('post.' + this.post.id)
                        .listen('.comment-available', (comment) => {
                            this.comments.unshift(comment);
                        });
                }
            }
        });
    </script>
@endsection